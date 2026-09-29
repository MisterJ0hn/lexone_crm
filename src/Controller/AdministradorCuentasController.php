<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Usuario;
use App\Entity\UsuarioCategoria;
use App\Entity\Empresa;
use App\Entity\UsuarioCuenta;
use App\Entity\Cuenta;
use App\Entity\UsuarioStatus;
use App\Entity\Privilegio;
use App\Entity\PrivilegioTipousuario;
use App\Form\UsuarioType;
use App\Repository\PrivilegioTipousuarioRepository;
use App\Repository\PrivilegioRepository;
use App\Repository\UsuarioRepository;
use App\Repository\UsuarioTipoRepository;
use App\Repository\ModuloPerRepository;
use App\Repository\UsuarioTipoDocumentoRepository;
use App\Repository\UsuarioNoDisponibleRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use Knp\Component\Pager\PaginatorInterface;
#[Route("/administrador_cuentas")]

class AdministradorCuentasController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }
 
    #[Route("/", name: "administrador_cuentas_index",methods: ["GET"])]
    public function index(UsuarioRepository $usuarioRepository,
                    ModuloPerRepository $moduloPerRepository,
                    PaginatorInterface $paginator,
                    UsuarioTipoRepository $usuarioTipoRepository,
                    Request $request): Response
    {
        $this->denyAccessUnlessGranted('view','administrador_cuentas');
        $user=$this->getUser();

        $modo=1;
        if($request->query->get('modo')=='trash'){
            $modo=0;
            
        }
       
        $pagina=$moduloPerRepository->findOneByName('administrador_cuentas',$user->getEmpresaActual());
        //$query=$usuarioRepository->findBy(['usuarioTipo'=>1,'estado'=>$modo]);

       
        $query=$usuarioRepository->findByEmpresa($user->getEmpresaActual(),['usuarioTipo'=>1,'estado'=>$modo]);

    
        $usuarios=$paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/,
            array('defaultSortFieldName' => 'nombre', 'defaultSortDirection' => 'asc'));

        return $this->render('administrador_cuentas/index.html.twig', [
            'usuarios' => $usuarios,
            'pagina'=>$pagina->getNombre(),
            'modo'=>$modo,
        ]);
    }

    #[Route("/new", name: "administrador_cuentas_new", methods: ["GET","POST"])]
    public function new(Request $request,
                        UserPasswordHasherInterface $encoder,
                        UsuarioTipoRepository $usuarioTipoRepository,
                        ModuloPerRepository $moduloPerRepository,
                        PrivilegioTipousuarioRepository $privilegioTipousuarioRepository,
                        PrivilegioRepository $privilegioRepository,
                        UsuarioTipoDocumentoRepository $tipoDocumento): Response
    {
        $this->denyAccessUnlessGranted('create','administrador_cuentas');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('administrador_cuentas',$user->getEmpresaActual());
        try{
        $usuario = new Usuario();
        $usuario->setEstado(1);
        // La empresa del nuevo administrador de cuentas es la empresa en la que
        // opera quien lo crea: asi las cuentas ofrecidas en el formulario y la
        // membresia resultante quedan en la empresa correcta (ver TenantSubscriber).
        $empresa=$this->entityManager->getRepository(Empresa::class)->find($user->getEmpresaActual());
         
        $cuentas=$empresa->getCuentas();
        
        
        $usuario->setUsuarioTipo($usuarioTipoRepository->find(1));
        $usuario->setFechaActivacion(new \DateTime(date('Y-m-d H:i:s')));
        
        $form = $this->createForm(UsuarioType::class, $usuario);
        $form->add('whatsapp',TextType::class);
        

        $form->add('sexo',ChoiceType::class,[
            'choices' =>[
                'Masculino'=>'Masculino',
                'Femenino'=>'Femenino'
            ],
        ]
        );
        $form->add("password", TextType::class);
        $form->add("passwordAnt", TextType::class,[
            'required'   => false,
            'attr'=>[
                'style'=>'display:none'
            ],
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->entityManager;
            
                $password=$usuario->getPassword();
                $encoded=$encoder->hashPassword($usuario,$password);
                $usuario->setPassword($encoded);

                $usuario->setUsername($usuario->getUsername().$user->getEmpresaActual());

                $usuario->setTipoDocumento($tipoDocumento->find($request->request->get('cboTipoDocumento')));

                $usuario->setFechaNacimiento(new \DateTime(date('Y-m-d H:i',strtotime($request->request->get('fecha_nacimiento')))));
                $usuario->setFechaActivacion(new \DateTime(date('Y-m-d H:i',strtotime($request->request->get('fecha_ingreso')))));

                $getcuentas=$_POST['cboEmpresa'];
                foreach($getcuentas as $getcuenta){
                    $cuenta=$this->entityManager->getRepository(Cuenta::class)->find($getcuenta);

                    $usuarioCuenta=new UsuarioCuenta();
                    $usuarioCuenta->setCuenta($cuenta);
                    $usuarioCuenta->setUsuario($usuario);
                    $entityManager->persist($usuarioCuenta);

                   
                    if(is_null($usuario->getEmpresaActual())){
                        $usuario->setEmpresaActual($cuenta->getEmpresa()->getId());
                    }
                }

                $entityManager->persist($usuario);
                $entityManager->flush();

                $privilegioTipousuarios=$privilegioTipousuarioRepository->findByEmpresa($user->getEmpresaActual(),$usuario->getUsuarioTipo()->getId());
                foreach($privilegioTipousuarios as $privilegioTipousuario){
                    $privilegio=$privilegioRepository->findBy(["moduloPer"=>$privilegioTipousuario->getModuloPer()->getId(),"usuario"=>$usuario->getId()]);
                    if(!$privilegio){
        
                        $privilegioNew=new Privilegio();
                        $privilegioNew->setUsuario($usuario);
                        $privilegioNew->setModuloPer($privilegioTipousuario->getModuloPer());
                        $privilegioNew->setAccion($privilegioTipousuario->getAccion());
        
                        $entityManager = $this->entityManager;
                        $entityManager->persist($privilegioNew);
                        $entityManager->flush();
        
                    }
                }
            
   

            return $this->redirectToRoute('administrador_cuentas_index');
        }
        }catch(\Exception $e){
            $this->addFlash('error','Error al crear el usuario. Error: '.$e->getMessage());
        }

        return $this->render('administrador_cuentas/new.html.twig', [
            'agendador' => $usuario,
            'form' => $form->createView(),
            'pagina'=>$pagina->getNombre(),
            'cuentas'=>$cuentas,
            'empresaActual'=>$user->getEmpresaActual(),
            'tipo_documentos'=>$tipoDocumento->findAll(),
        ]);
    }

    #[Route("/{id}", name: "administrador_cuentas_show", methods: ["GET"])]
    public function show(Usuario $usuario,ModuloPerRepository $moduloPerRepository): Response
    {
        $this->denyAccessUnlessGranted('view','administrador_cuentas');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('administrador_cuentas',$user->getEmpresaActual());
        return $this->render('usuario/show.html.twig', [
            'usuario' => $usuario,
            'pagina'=>$pagina->getNombre(),
        ]);
    }

    #[Route("/{id}/edit", name: "administrador_cuentas_edit", methods: ["GET","POST"])]
    public function edit(Request $request, 
                        Usuario $usuario,
                        UsuarioTipoRepository $usuarioTipoRepository,
                        ModuloPerRepository $moduloPerRepository,
                        UsuarioTipoDocumentoRepository $tipoDocumento,
                        UserPasswordHasherInterface $encoder,
                        UsuarioNoDisponibleRepository $usuarioNoDisponibleRepository): Response
    {
        $this->denyAccessUnlessGranted('edit','administrador_cuentas');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('administrador_cuentas',$user->getEmpresaActual());
        $empresa=$this->entityManager->getRepository(Empresa::class)->find($user->getEmpresaActual());
        $usuarioCuenta=$this->entityManager->getRepository(UsuarioCuenta::class)->findOneBy(['usuario'=>$usuario->getId()]);
   
        $usuarioCategorias=$empresa->getUsuarioCategorias();
        $cuentas=$empresa->getCuentas();

        $statues=$this->entityManager->getRepository(UsuarioStatus::class)->findBy(['id'=>[1,2]]);
        $form = $this->createForm(UsuarioType::class, $usuario);

        $form->add("password", TextType::class,[
            'required'   => false,
            'attr'=>[
                'style'=>'display:none'
            ],

        ]);
        $form->add("passwordAnt", TextType::class,[
            'required'   => false,
        ]);
        $form->add('sexo',ChoiceType::class,[
            'choices' =>[
                'Masculino'=>'Masculino',
                'Femenino'=>'Femenino'
            ],
        ]
        );

        $form->handleRequest($request);

        $horaInicio=$usuarioNoDisponibleRepository->getHoras();
        $horaFin=$usuarioNoDisponibleRepository->getHoras();

        if ($form->isSubmitted() && $form->isValid()) {
            if($usuario->getPasswordAnt()!=""){
                $password=$usuario->getPasswordAnt();
                $encoded=$encoder->hashPassword($usuario,$password);
                $usuario->setPassword($encoded);
                $usuario->setPasswordAnt("");
            }
            
            $this->entityManager->flush();
            
            $entityManager = $this->entityManager;

            //$categoria=$this->entityManager->getRepository(UsuarioCategoria::class)->find($request->request->get('cboUsuarioCategoria'));
            //$usuario->setCategoria($categoria);


            $usuario->setTipoDocumento($tipoDocumento->find($request->request->get('cboTipoDocumento')));

            //$status=$this->entityManager->getRepository(UsuarioStatus::class)->find($request->request->get('cboStatues'));
            //$usuario->setStatus($status);

            $usuario->setFechaNacimiento(new \DateTime(date('Y-m-d H:i',strtotime($request->request->get('fecha_nacimiento')))));
            $usuario->setFechaActivacion(new \DateTime(date('Y-m-d H:i',strtotime($request->request->get('fecha_ingreso')))));

            $usuarioCuentas=$usuario->getUsuarioCuentas();
            foreach($usuarioCuentas as $usuarioCuenta){
                $usuario->removeUsuarioCuenta($usuarioCuenta);
            }
            $getcuentas=$_POST['cboEmpresa'];
         
            foreach($getcuentas as $getcuenta){
                $cuenta=$this->entityManager->getRepository(Cuenta::class)->find($getcuenta);
                
                $usuarioCuenta=new UsuarioCuenta();

                $usuarioCuenta->setCuenta($cuenta);
                $usuarioCuenta->setUsuario($usuario);

                $entityManager->persist($usuarioCuenta);
                $entityManager->flush();
                $usuario->setEmpresa($cuenta->getEmpresa());
                if(is_null($usuario->getEmpresaActual())){
                    $usuario->setEmpresaActual($cuenta->getEmpresa()->getId());
                }
            }
            $entityManager->persist($usuario);
            $entityManager->flush();

            return $this->redirectToRoute('administrador_cuentas_index');
        }
        $id_cuenta=null;
        if($usuarioCuenta !== null){
            $id_cuenta=$usuarioCuenta->getCuenta()->getId();
        }

        return $this->render('administrador_cuentas/edit.html.twig', [
            'usuario' => $usuario,
            'form' => $form->createView(),
            'pagina'=>$pagina->getNombre(),
            'cuentas'=>$cuentas,
            'usuarioCategorias'=>$usuarioCategorias,
            'empresaActual'=>$user->getEmpresaActual(),
            'statues'=>$statues,
            'id_cuenta'=>$id_cuenta,
            'cuentas_sel'=>$usuario->getUsuarioCuentas(),
            'tipo_documentos'=>$tipoDocumento->findAll(),
            'hora_inicio'=>$horaInicio,
            'hora_fin'=>$horaFin,
        ]);
    }
    #[Route("/{id}/restore", name: "administrador_cuentas_restore", methods: ["GET"])]
    public function restore(Request $request, Usuario $usuario): Response
    {
        $this->denyAccessUnlessGranted('full','administrador_cuentas');
      
            $entityManager = $this->entityManager;
            $usuario->setEstado(1);
            $entityManager->persist($usuario);
            $entityManager->flush();
     

        return $this->redirectToRoute('administrador_cuentas_index');
    }

    #[Route("/{id}", name: "administrador_cuentas_delete", methods: ["DELETE"])]
    public function delete(Request $request, Usuario $usuario): Response
    {
        $this->denyAccessUnlessGranted('full','administrador_cuentas');
        if ($this->isCsrfTokenValid('delete'.$usuario->getId(), $request->request->get('_token'))) {
            $entityManager = $this->entityManager;
            $usuario->setEstado(0);
            $entityManager->persist($usuario);
            $entityManager->flush();
        }

        return $this->redirectToRoute('administrador_cuentas_index');
    }
}

