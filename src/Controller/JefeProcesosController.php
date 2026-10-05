<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Usuario;
use App\Entity\Empresa;
use App\Entity\UsuarioCuenta;
use App\Entity\Cuenta;
use App\Entity\Privilegio;
use App\Form\UsuarioType;
use App\Repository\UsuarioRepository;
use App\Repository\UsuarioTipoRepository;
use App\Repository\ModuloPerRepository;
use App\Repository\UsuarioTipoDocumentoRepository;
use App\Repository\PrivilegioTipousuarioRepository;
use App\Repository\PrivilegioRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use Knp\Component\Pager\PaginatorInterface;
#[Route("/jefe_procesos")]
class JefeProcesosController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }
    #[Route("/", name: "jefe_procesos_index",methods: ["GET"])]
    public function index(UsuarioRepository $usuarioRepository,
                    ModuloPerRepository $moduloPerRepository,
                    PaginatorInterface $paginator,
                    Request $request): Response
    {
        $this->denyAccessUnlessGranted('view','jefe_procesos');
        $user=$this->getUser();
        $modo=1;
        if($request->query->get('modo')=='trash'){
            $modo=0;
            
        }
        $pagina=$moduloPerRepository->findOneByName('jefe_procesos',1);
        $query=$usuarioRepository->findByEmpresa($user->getEmpresaActual(),['usuarioTipo'=>3,'estado'=>$modo]);
        $usuarios=$paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/,
            array('defaultSortFieldName' => 'nombre', 'defaultSortDirection' => 'asc'));

        return $this->render('jefe_procesos/index.html.twig', [
            'usuarios' => $usuarios,
            'pagina'=>$pagina->getNombre(),
            'modo'=>$modo,
        ]);
    }

    #[Route("/new", name: "jefe_procesos_new", methods: ["GET","POST"])]
    public function new(Request $request,
                        UserPasswordHasherInterface $encoder,
                        UsuarioTipoRepository $usuarioTipoRepository,
                        ModuloPerRepository $moduloPerRepository,
                        PrivilegioTipousuarioRepository $privilegioTipousuarioRepository,
                        PrivilegioRepository $privilegioRepository,
                        UsuarioTipoDocumentoRepository $tipoDocumento): Response
    {
        $this->denyAccessUnlessGranted('create','jefe_procesos');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('jefe_procesos',1);
        $usuario = new Usuario();
        $usuario->setEstado(1);
        $empresa=$this->entityManager->getRepository(Empresa::class)->find($user->getEmpresaActual());
       
        $cuentas=$empresa->getCuentas();
        
        $usuario->setUsuarioTipo($usuarioTipoRepository->find(3));
        $usuario->setFechaActivacion(new \DateTime(date('Y-m-d H:i:s')));
        
        $form = $this->createForm(UsuarioType::class, $usuario);
        $form->add('whatsapp',TextType::class);
        
        $form->add("password", TextType::class);
        $form->add("passwordAnt", TextType::class,[
            'required'   => false,
            'attr'=>[
                'style'=>'display:none'
            ],
        ]);

        $form->add('sexo',ChoiceType::class,[
            'choices' =>[
                'Masculino'=>'Masculino',
                'Femenino'=>'Femenino'
            ],
        ]
        );
        

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->entityManager;

            try{
                $password=$usuario->getPassword();
                $encoded=$encoder->hashPassword($usuario,$password);
                $usuario->setPassword($encoded);
                $usuario->setUsername($usuario->getUsername().$user->getEmpresaActual());
            
                $usuarioCuenta=new UsuarioCuenta();
        
                $usuario->setTipoDocumento($tipoDocumento->find($request->request->get('cboTipoDocumento')));

                $usuario->setFechaNacimiento(new \DateTime(date('Y-m-d H:i',strtotime($request->request->get('fecha_nacimiento')))));
                $usuario->setFechaActivacion(new \DateTime(date('Y-m-d H:i',strtotime($request->request->get('fecha_ingreso')))));
            
                $entityManager->persist($usuario);
                $entityManager->flush();

                $getcuenta=$_POST['cboEmpresa'];
            
                $cuenta=$this->entityManager->getRepository(Cuenta::class)->find($getcuenta);
                $usuarioCuenta=new UsuarioCuenta();
                $usuarioCuenta->setCuenta($cuenta);
                $usuarioCuenta->setUsuario($usuario);
                $entityManager->persist($usuarioCuenta);
                $entityManager->flush();
                $usuario->setEmpresa($cuenta->getEmpresa());
                $usuario->setEmpresaActual($user->getEmpresaActual());
                $entityManager->persist($usuario);
                $entityManager->flush();
            

                $privilegioTipousuarios=$privilegioTipousuarioRepository->findBy(['tipousuario'=>$usuario->getUsuarioTipo()->getId()]);
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
                
                $this->addFlash('success','Usuario creado con éxito');
            }catch(Exception $e){
                $this->addFlash('error','ha ocurrido un error al crear el usuario: '.$e->getMessage());
            }
            return $this->redirectToRoute('jefe_procesos_index');
        }

        return $this->render('jefe_procesos/new.html.twig', [
            'usuario' => $usuario,
            'form' => $form->createView(),
            'pagina'=>$pagina->getNombre(),
            'cuentas'=>$cuentas,
            'empresaActual'=>$user->getEmpresaActual(),
            'tipo_documentos'=>$tipoDocumento->findAll(),
            
        ]);
    }

    #[Route("/{id}", name: "jefe_procesos_show", methods: ["GET"])]
    public function show(Usuario $usuario,ModuloPerRepository $moduloPerRepository): Response
    {
        $this->denyAccessUnlessGranted('view','jefe_procesos');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('jefe_procesos',$user->getEmpresaActual());
        return $this->render('usuario/show.html.twig', [
            'usuario' => $usuario,
            'pagina'=>$pagina->getNombre(),
        ]);
    }

    #[Route("/{id}/edit", name: "jefe_procesos_edit", methods: ["GET","POST"])]
    public function edit(Request $request, Usuario $usuario,UserPasswordHasherInterface $encoder,UsuarioTipoRepository $usuarioTipoRepository,ModuloPerRepository $moduloPerRepository,
    UsuarioTipoDocumentoRepository $tipoDocumento): Response
    {
        $this->denyAccessUnlessGranted('edit','jefe_procesos');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('jefe_procesos',1);
        $empresa=$this->entityManager->getRepository(Empresa::class)->find(1);
        $usuarioCuenta=$this->entityManager->getRepository(UsuarioCuenta::class)->findOneBy(['usuario'=>$usuario->getId()]);
   
        $cuentas=$empresa->getCuentas();
        //$usuario->setPasswordAnt($usuario->getPassword());
        //$usuario->setPassword('');
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

        if ($form->isSubmitted() && $form->isValid()) {

            if($usuario->getPasswordAnt()!=""){
                $password=$usuario->getPasswordAnt();
                $encoded=$encoder->hashPassword($usuario,$password);
                $usuario->setPassword($encoded);
                $usuario->setPasswordAnt("");
            }
            
            $this->entityManager->flush();
            

            $entityManager = $this->entityManager;

            

            $usuario->setTipoDocumento($tipoDocumento->find($request->request->get('cboTipoDocumento')));

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
                    $usuario->setEmpresaActual($user->getEmpresaActual());
                }
            }
            $entityManager->persist($usuario);
            $entityManager->flush();
            return $this->redirectToRoute('jefe_procesos_index');
        }
        $id_cuenta=null;
        if($usuarioCuenta !== null){
            $id_cuenta=$usuarioCuenta->getCuenta()->getId();
        }
        
        return $this->render('jefe_procesos/edit.html.twig', [
            'usuario' => $usuario,
            'form' => $form->createView(),
            'pagina'=>$pagina->getNombre(),
            'cuentas'=>$cuentas,
            'id_cuenta'=>$id_cuenta,
            'empresaActual'=>$user->getEmpresaActual(),
            'tipo_documentos'=>$tipoDocumento->findAll(),
            'cuentas_sel'=>$usuario->getUsuarioCuentas(),
        ]);
    }
    #[Route("/{id}/restore", name: "jefe_procesos_restore", methods: ["GET"])]
    public function restore(Request $request, Usuario $usuario, UsuarioRepository $usuarioRepository): Response
    {
        $this->denyAccessUnlessGranted('full','jefe_procesos');
        try{
            $usuarioRepository->restaurarUsername($usuario);
        } catch (\Exception $e) {
            $usuarioexistente=$usuarioRepository->findOneBy(['username'=>$usuario->getUsernameOriginal()]);
            if($usuarioexistente){
                $this->addFlash('error', 'No se puede restaurar el username original del usuario porque ya existe otro usuario con ese username. Nombre Usuario: '.$usuarioexistente->getNombre().' - Perfil: '.$usuarioexistente->getUsuarioTipo()->getNombre());

            }else{
                $this->addFlash('error', 'Error al restaurar el username original del usuario. Por favor, contacte al administrador del sistema. Error: '.$e->getMessage());
            }
                return $this->redirectToRoute('jefe_procesos_index');
        }
        $entityManager = $this->entityManager;
        $usuario->setEstado(1);
        $entityManager->persist($usuario);
        $entityManager->flush();
     

        return $this->redirectToRoute('jefe_procesos_index');
    }
    #[Route("/{id}", name: "jefe_procesos_delete", methods: ["DELETE"])]
    public function delete(Request $request, Usuario $usuario, UsuarioRepository $usuarioRepository): Response
    {
        $this->denyAccessUnlessGranted('full','jefe_procesos');
        if ($this->isCsrfTokenValid('delete'.$usuario->getId(), $request->request->get('_token'))) {
            $usuarioRepository->respaldarUsername($usuario);
            $entityManager = $this->entityManager;
            $usuario->setEstado(0);
            $entityManager->persist($usuario);
            $entityManager->flush();
        }

        return $this->redirectToRoute('jefe_procesos_index');
    }
}
