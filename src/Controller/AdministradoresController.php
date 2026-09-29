<?php
namespace App\Controller;
use App\Entity\Usuario;
use App\Entity\UsuarioCategoria;
use App\Entity\Empresa;
use App\Entity\UsuarioCuenta;
use App\Entity\Cuenta;
use App\Entity\UsuarioStatus;
use App\Entity\Privilegio;
use App\Entity\PrivilegioTipousuario;
use App\Form\UsuarioType;
use App\Repository\EmpresaRepository;
use App\Repository\PrivilegioTipousuarioRepository;
use App\Repository\PrivilegioRepository;
use App\Repository\UsuarioRepository;
use App\Repository\UsuarioTipoRepository;
use App\Repository\ModuloPerRepository;
use App\Repository\UsuarioCategoriaRepository;
use App\Repository\UsuarioTipoDocumentoRepository;
use App\Repository\UsuarioNoDisponibleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use Knp\Component\Pager\PaginatorInterface;
#[Route("/administradores")]

class AdministradoresController extends AbstractController
{
    #[Route("/", name: "administradores_index",methods: ["GET"])]
    public function index(UsuarioRepository $usuarioRepository,
                    ModuloPerRepository $moduloPerRepository,
                    PaginatorInterface $paginator,
                    Request $request): Response
    {
        $this->denyAccessUnlessGranted('view','administradores');
        $user=$this->getUser();

        $modo=1;
        if($request->query->get('modo')=='trash'){
            $modo=0;
            
        }
        $pagina=$moduloPerRepository->findOneByName('administradores',$user->getEmpresaActual());
        $query=$usuarioRepository->findBy(['usuarioTipo'=>8,'estado'=>$modo]);
        $usuarios=$paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/,
            array('defaultSortFieldName' => 'nombre', 'defaultSortDirection' => 'asc'));

        return $this->render('administradores/index.html.twig', [
            'usuarios' => $usuarios,
            'pagina'=>$pagina->getNombre(),
            'modo'=>$modo,
        ]);
    }

    #[Route("/new", name: "administradores_new", methods: ["GET","POST"])]
    public function new(Request $request,
                        UserPasswordEncoderInterface $encoder,
                        UsuarioTipoRepository $usuarioTipoRepository,
                        ModuloPerRepository $moduloPerRepository,
                        PrivilegioTipousuarioRepository $privilegioTipousuarioRepository,
                        PrivilegioRepository $privilegioRepository,
                        UsuarioTipoDocumentoRepository $tipoDocumento): Response
    {
        $this->denyAccessUnlessGranted('create','administradores');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('administradores',$user->getEmpresaActual());
        $usuario = new Usuario();
        $usuario->setEstado(1);
        $empresa=$this->getDoctrine()->getRepository(Empresa::class)->find($user->getEmpresaActual());
         
        $cuentas=$empresa->getCuentas();
        
        
        $usuario->setUsuarioTipo($usuarioTipoRepository->find(8));
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
            $entityManager = $this->getDoctrine()->getManager();
            $password=$usuario->getPassword();
            $encoded=$encoder->encodePassword($usuario,$password);
            $usuario->setPassword($encoded);

           
            $usuarioCuenta=new UsuarioCuenta();
            $usuario->setTipoDocumento($tipoDocumento->find($request->request->get('cboTipoDocumento')));
            $usuario->setFechaNacimiento(new \DateTime(date('Y-m-d H:i',strtotime($request->request->get('fecha_nacimiento')))));
            
           
            $entityManager->persist($usuario);
            $entityManager->flush();

            $getcuentas=$_POST['cboEmpresa'];
         
            foreach($getcuentas as $getcuenta){
                $cuenta=$this->getDoctrine()->getRepository(Cuenta::class)->find($getcuenta);
                
                $usuarioCuenta=new UsuarioCuenta();

                $usuarioCuenta->setCuenta($cuenta);
                $usuarioCuenta->setUsuario($usuario);

                $entityManager->persist($usuarioCuenta);
                $entityManager->flush();
                $usuario->setEmpresa($cuenta->getEmpresa());
                $usuario->setEmpresaActual($cuenta->getEmpresa()->getId());
                $entityManager->persist($usuario);
                $entityManager->flush();
            }

            $privilegioTipousuarios=$privilegioTipousuarioRepository->findBy(['tipousuario'=>$usuario->getUsuarioTipo()->getId()]);
            foreach($privilegioTipousuarios as $privilegioTipousuario){
                $privilegio=$privilegioRepository->findBy(["moduloPer"=>$privilegioTipousuario->getModuloPer()->getId(),"usuario"=>$usuario->getId()]);
                if(!$privilegio){
    
                    $privilegioNew=new Privilegio();
                    $privilegioNew->setUsuario($usuario);
                    $privilegioNew->setModuloPer($privilegioTipousuario->getModuloPer());
                    $privilegioNew->setAccion($privilegioTipousuario->getAccion());
    
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($privilegioNew);
                    $entityManager->flush();
    
                }
            }
   

            return $this->redirectToRoute('administradores_index');
        }

        return $this->render('administradores/new.html.twig', [
            'agendador' => $usuario,
            'form' => $form->createView(),
            'pagina'=>$pagina->getNombre(),
            'cuentas'=>$cuentas,
        
            'tipo_documentos'=>$tipoDocumento->findAll(),
        ]);
    }

    #[Route("/{id}", name: "administradores_show", methods: ["GET"])]
    public function show(Usuario $usuario,ModuloPerRepository $moduloPerRepository): Response
    {
        $this->denyAccessUnlessGranted('view','administradores');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('administradores',$user->getEmpresaActual());
        return $this->render('usuario/show.html.twig', [
            'usuario' => $usuario,
            'pagina'=>$pagina->getNombre(),
        ]);
    }

    #[Route("/{id}/edit", name: "administradores_edit", methods: ["GET","POST"])]
    public function edit(Request $request, 
                        Usuario $usuario,
                        UsuarioTipoRepository $usuarioTipoRepository,
                        ModuloPerRepository $moduloPerRepository,
                        UsuarioTipoDocumentoRepository $tipoDocumento,
                        UserPasswordEncoderInterface $encoder,
                        UsuarioNoDisponibleRepository $usuarioNoDisponibleRepository,
                        UsuarioCategoriaRepository $usuarioCategoriaRepository,
                        EmpresaRepository $empresaRepository): Response
    {
        $this->denyAccessUnlessGranted('edit','administradores');
        $user=$this->getUser();
        $pagina=$moduloPerRepository->findOneByName('administradores',$user->getEmpresaActual());
        $empresa=$empresaRepository->find($user->getEmpresaActual());
        $usuarioCuenta=$this->getDoctrine()->getRepository(UsuarioCuenta::class)->findOneBy(['usuario'=>$usuario->getId()]);
   
        $usuarioCategorias=$empresa->getUsuarioCategorias();
        $cuentas=$empresa->getCuentas();

        $statues=$this->getDoctrine()->getRepository(UsuarioStatus::class)->findBy(['id'=>[1,2]]);
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
                $encoded=$encoder->encodePassword($usuario,$password);
                $usuario->setPassword($encoded);
                $usuario->setPasswordAnt("");
            }
            
            $this->getDoctrine()->getManager()->flush();
            
            $entityManager = $this->getDoctrine()->getManager();

            $categoria=$usuarioCategoriaRepository->find(1);
            $usuario->setCategoria($categoria);
            $usuario->setFechaNacimiento(new \DateTime(date('Y-m-d H:i',strtotime($request->request->get('fecha_nacimiento')))));
            

            $usuario->setTipoDocumento($tipoDocumento->find($request->request->get('cboTipoDocumento')));

            /*$status=$this->getDoctrine()->getRepository(UsuarioStatus::class)->find($request->request->get('cboStatues'));
            $usuario->setStatus($status);
            */

            $usuarioCuentas=$usuario->getUsuarioCuentas();
            foreach($usuarioCuentas as $usuarioCuenta){
                $usuario->removeUsuarioCuenta($usuarioCuenta);
            }
            $getcuentas=$_POST['cboEmpresa'];
         
            foreach($getcuentas as $getcuenta){
                $cuenta=$this->getDoctrine()->getRepository(Cuenta::class)->find($getcuenta);
                
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
            
            return $this->redirectToRoute('administradores_index');
        }

        return $this->render('administradores/edit.html.twig', [
            'usuario' => $usuario,
            'form' => $form->createView(),
            'pagina'=>$pagina->getNombre(),
            'cuentas'=>$cuentas,
            'usuarioCategorias'=>$usuarioCategorias,

            'statues'=>$statues,
            'id_cuenta'=>$usuarioCuenta->getCuenta()->getId(),
            'cuentas_sel'=>$usuario->getUsuarioCuentas(),
            'tipo_documentos'=>$tipoDocumento->findAll(),
            'hora_inicio'=>$horaInicio,
            'hora_fin'=>$horaFin,
        ]);
    }
    #[Route("/{id}/restore", name: "administradores_restore", methods: ["GET"])]
    public function restore(Request $request, Usuario $usuario): Response
    {
        $this->denyAccessUnlessGranted('full','administradores');
      
            $entityManager = $this->getDoctrine()->getManager();
            $usuario->setEstado(1);
            $entityManager->persist($usuario);
            $entityManager->flush();
     

        return $this->redirectToRoute('administradores_index');
    }

    #[Route("/{id}", name: "administradores_delete", methods: ["DELETE"])]
    public function delete(Request $request, Usuario $usuario): Response
    {
        $this->denyAccessUnlessGranted('full','administradores');
        if ($this->isCsrfTokenValid('delete'.$usuario->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $usuario->setEstado(0);
            $entityManager->persist($usuario);
            $entityManager->flush();
        }

        return $this->redirectToRoute('administradores_index');
    }
}

