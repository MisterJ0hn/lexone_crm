<?php

namespace App\Controller;

use App\Entity\CorreoBienvenida;
use App\Entity\TipoCliente;
use App\Form\CorreoBienvenidaType;
use App\Repository\CorreoBienvenidaRepository;
use App\Repository\EmpresaRepository;
use App\Repository\TipoClienteRepository;
use App\Service\ContratoTemplateRenderer;
use App\Service\CorreoBienvenidaService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Configuración del correo de bienvenida (template HTML) por tipo de cliente de
 * la empresa actual. Se envía desde CorreoBienvenidaService al crear un contrato.
 * Reutiliza los permisos del módulo 'contrato_template'.
 */
#[Route("/correo_bienvenida")]
class CorreoBienvenidaController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        #[Autowire('%kernel.project_dir%/var/correo_bienvenida')] private readonly string $directorioImagenes,
    ) {
    }

    #[Route("/", name: "correo_bienvenida_index", methods: ["GET"])]
    public function index(TipoClienteRepository $tipoClienteRepository, CorreoBienvenidaRepository $correoRepository, EmpresaRepository $empresaRepository): Response
    {
        $this->denyAccessUnlessGranted('view', 'contrato_template');
        $empresaId = $this->getUser()->getEmpresaActual();

        $filas = [];
        foreach ($tipoClienteRepository->findBy([], ['id' => 'ASC']) as $tipo) {
            $filas[] = [
                'tipo' => $tipo,
                'correo' => $correoRepository->findOneBy(['empresa' => $empresaId, 'tipoCliente' => $tipo]),
            ];
        }

        return $this->render('correo_bienvenida/index.html.twig', [
            'filas' => $filas,
            'empresa' => $empresaRepository->find($empresaId),
        ]);
    }

    /**
     * Guarda el SMTP de la empresa actual (con el que se envían los correos y del que sale el remitente).
     * Contraseña vacía = conservar la actual. Responde JSON para el popup del listado.
     */
    #[Route("/remitente", name: "correo_bienvenida_remitente", methods: ["POST"])]
    public function remitente(Request $request, EmpresaRepository $empresaRepository): JsonResponse
    {
        $this->denyAccessUnlessGranted('edit', 'contrato_template');
        if (!$this->isCsrfTokenValid('correo_bienvenida_remitente', (string) $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['detail' => 'Token inválido, recargue la página.'], 400);
        }

        $host = trim((string) $request->request->get('host'));
        $puerto = (int) $request->request->get('puerto');
        $cifrado = (string) $request->request->get('cifrado');
        $usuario = trim((string) $request->request->get('usuario'));
        $password = (string) $request->request->get('password');
        $correo = trim((string) $request->request->get('remitenteCorreo'));
        $nombre = trim((string) $request->request->get('remitenteNombre'));

        if ($host === '' || !preg_match('/^[A-Za-z0-9.\-]+$/', $host)) {
            return new JsonResponse(['detail' => 'Ingrese un servidor SMTP válido (ej. smtp.midominio.cl).'], 422);
        }
        if ($puerto < 1 || $puerto > 65535) {
            return new JsonResponse(['detail' => 'El puerto debe estar entre 1 y 65535.'], 422);
        }
        if (!in_array($cifrado, ['ssl', 'tls', 'ninguno'], true)) {
            return new JsonResponse(['detail' => 'Seleccione el tipo de cifrado.'], 422);
        }
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['detail' => 'Ingrese un correo de remitente válido.'], 422);
        }
        if (mb_strlen($nombre) > 150 || mb_strlen($usuario) > 255 || mb_strlen($password) > 255) {
            return new JsonResponse(['detail' => 'Alguno de los datos es demasiado largo.'], 422);
        }

        $empresa = $empresaRepository->find($this->getUser()->getEmpresaActual());
        $empresa->setSmtpHost($host)
            ->setSmtpPuerto($puerto)
            ->setSmtpCifrado($cifrado)
            ->setSmtpUsuario($usuario !== '' ? $usuario : null)
            ->setSmtpRemitenteCorreo($correo)
            ->setSmtpRemitenteNombre($nombre !== '' ? $nombre : null);
        if ($usuario === '') {
            $empresa->setSmtpPassword(null);
        } elseif ($password !== '') {
            $empresa->setSmtpPassword($password);
        }
        $this->entityManager->flush();

        return new JsonResponse(['ok' => true]);
    }

    #[Route("/{id}/edit", name: "correo_bienvenida_edit", requirements: ['id' => '\d+'], methods: ["GET","POST"])]
    public function edit(TipoCliente $tipo, Request $request, CorreoBienvenidaRepository $correoRepository, EmpresaRepository $empresaRepository): Response
    {
        $this->denyAccessUnlessGranted('edit', 'contrato_template');
        $empresa = $empresaRepository->find($this->getUser()->getEmpresaActual());

        $correo = $correoRepository->findOneBy(['empresa' => $empresa, 'tipoCliente' => $tipo]);
        if ($correo === null) {
            $correo = (new CorreoBienvenida())->setEmpresa($empresa)->setTipoCliente($tipo)->setActivo(false);
        }

        $form = $this->createForm(CorreoBienvenidaType::class, $correo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $archivo = $form->get('archivo')->getData();
            if ($archivo !== null) {
                $correo->setContenido((string) file_get_contents($archivo->getPathname()));
            }
            $this->actualizarImagen($correo, $form->get('imagenArchivo')->getData(), (bool) $form->get('quitarImagen')->getData());
            if (trim((string) $correo->getContenido()) === '') {
                $this->addFlash('error', 'Debe subir un archivo HTML o escribir el contenido del correo.');
            } else {
                $correo->setFechaModificacion(new \DateTime());
                $this->entityManager->persist($correo);
                $this->entityManager->flush();
                $this->addFlash('success', 'Correo de bienvenida guardado.');
                return $this->redirectToRoute('correo_bienvenida_index');
            }
        }

        return $this->render('correo_bienvenida/edit.html.twig', [
            'tipo' => $tipo,
            'correo' => $correo,
            'form' => $form->createView(),
            'variables' => ContratoTemplateRenderer::catalogo(),
            'ejemplo' => CorreoBienvenidaService::escapar(CorreoBienvenidaService::ejemplo((string) $tipo)),
            'imagenDatos' => $this->imagenComoDatos($correo),
            'smtpRemitente' => $empresa->tieneSmtp() ? $empresa->getSmtpRemitenteCorreo() : null,
            'smtpRemitenteNombre' => $empresa->getSmtpRemitenteNombre(),
        ]);
    }

    /**
     * Envía un correo de prueba con datos de ejemplo a una dirección, usando lo que hay en el formulario
     * (asunto, remitente, HTML e imagen), guardado o no. Responde JSON para la vista previa.
     */
    #[Route("/{id}/prueba", name: "correo_bienvenida_prueba", requirements: ['id' => '\d+'], methods: ["POST"])]
    public function prueba(TipoCliente $tipo, Request $request, CorreoBienvenidaRepository $correoRepository, CorreoBienvenidaService $servicio, EmpresaRepository $empresaRepository): JsonResponse
    {
        $this->denyAccessUnlessGranted('edit', 'contrato_template');
        if (!$this->isCsrfTokenValid('correo_bienvenida_prueba', (string) $request->headers->get('X-CSRF-Token'))) {
            return new JsonResponse(['detail' => 'Token inválido, recargue la página.'], 400);
        }

        $destino = trim((string) $request->request->get('destino'));
        $asunto = trim((string) $request->request->get('asunto'));
        $html = (string) $request->request->get('contenido');
        if (!filter_var($destino, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['detail' => 'Ingrese un correo de destino válido.'], 422);
        }
        $empresa = $empresaRepository->find($this->getUser()->getEmpresaActual());
        if (!$empresa->tieneSmtp()) {
            return new JsonResponse(['detail' => 'La empresa no tiene el SMTP configurado (servidor, puerto y correo del remitente).'], 422);
        }
        if ($asunto === '' || trim($html) === '') {
            return new JsonResponse(['detail' => 'El asunto y el contenido del correo no pueden estar vacíos.'], 422);
        }
        if (strlen($html) > 1024 * 1024) {
            return new JsonResponse(['detail' => 'El contenido supera 1 MB.'], 422);
        }

        // Imagen: la que se acaba de elegir (si es válida), si no la guardada, salvo que se haya pedido quitarla.
        $rutaImagen = null;
        $archivo = $request->files->get('imagenArchivo');
        if ($archivo instanceof UploadedFile) {
            if (!$archivo->isValid() || $archivo->getSize() > 2 * 1024 * 1024 || !in_array($archivo->getMimeType(), ['image/png', 'image/jpeg', 'image/gif'], true)) {
                return new JsonResponse(['detail' => 'La imagen debe ser PNG, JPG o GIF de hasta 2 MB.'], 422);
            }
            $rutaImagen = $archivo->getPathname();
        } elseif (!$request->request->getBoolean('quitarImagen')) {
            $guardado = $correoRepository->findOneBy(['empresa' => $this->getUser()->getEmpresaActual(), 'tipoCliente' => $tipo]);
            if ($guardado?->getImagen()) {
                $rutaImagen = $this->directorioImagenes . '/' . $guardado->getImagen();
            }
        }

        $error = $servicio->enviarPrueba($empresa, $destino, trim((string) $request->request->get('remitenteNombre')) ?: null, $asunto, $html, $rutaImagen, (string) $tipo);
        if ($error !== null) {
            return new JsonResponse(['detail' => 'No se pudo enviar la prueba: ' . $error], 502);
        }

        return new JsonResponse(['ok' => true, 'destino' => $destino]);
    }
    /** La imagen guardada como data URI, para la vista previa (un iframe aislado no envía la sesión). */
    private function imagenComoDatos(CorreoBienvenida $correo): ?string
    {
        $ruta = $correo->getImagen() ? $this->directorioImagenes . '/' . $correo->getImagen() : null;
        if ($ruta === null || !is_file($ruta)) {
            return null;
        }
        $mime = ['png' => 'image/png', 'jpg' => 'image/jpeg', 'gif' => 'image/gif'][strtolower(pathinfo($ruta, PATHINFO_EXTENSION))] ?? 'application/octet-stream';

        return 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($ruta));
    }

    /** Guarda/reemplaza/quita la imagen (nombre aleatorio; solo se conserva la extensión validada). */
    private function actualizarImagen(CorreoBienvenida $correo, ?UploadedFile $archivo, bool $quitar): void
    {
        if ($archivo === null && !$quitar) {
            return;
        }
        if ($correo->getImagen()) {
            @unlink($this->directorioImagenes . '/' . $correo->getImagen());
            $correo->setImagen(null);
        }
        if ($archivo !== null) {
            $extension = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/gif' => 'gif'][$archivo->getMimeType()] ?? 'png';
            $nombre = bin2hex(random_bytes(16)) . '.' . $extension;
            $archivo->move($this->directorioImagenes, $nombre);
            $correo->setImagen($nombre);
        }
    }
}
