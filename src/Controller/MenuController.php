<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Menu;
use App\Entity\MenuCabezera;
use App\Repository\MenuCabezeraRepository;
use App\Repository\EmpresaRepository;
use App\Form\MenuType;
use App\Repository\MenuRepository;
use App\Repository\ModuloPerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/menu")]
class MenuController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }
    
    #[Route("/main", name: "menu_main", methods: ["GET","POST"])]
    public function mainMenu(String $route_name,
                        MenuRepository $menuRepository,
                        EmpresaRepository $empresaRepository, 
                        ModuloPerRepository $moduloPerRepository)
    {
        $user=$this->getUser();

        $menuCabezera=$user->getUsuarioTipo()->getMenuCabezera();
        
       
      /*  $items['menu']['title']="Salir";
        $items['menu']['url']="/logout";
        $items['menu']['icon']="fas fa-sign-out-alt";
        $items['menu']['class']="";
        $items['menu']['raiz1']='';
        $items['menu']['raiz2']='';
        $items['menu']['menuraiz']=0;

        $subitems['menu']['title']="";
        $subitems['menu']['url']="";
        $subitems['menu']['icon']="";
        $subitems['menu']['class']="";
        $subitems['menu']['menuraiz']=0;*/
        
        $items=array();
        $subitems=array();
        if($menuCabezera){
            $menus=$menuRepository->findBy(['dependeDe'=>null,'menuCabezera'=>$menuCabezera->getId(),'empresa'=>1],['orden'=>'ASC']);
            
            /*if($user->getId()==1){
                $menus=$menuRepository->findBy(['dependeDe'=>null,'menuCabezera'=>$menuCabezera->getId()],['orden'=>'ASC']);
            
            }else{
                $menus=$menuRepository->findBy(['dependeDe'=>null,'menuCabezera'=>$menuCabezera->getId(),'empresa'=>$user->getEmpresaActual()],['orden'=>'ASC']);
            
            }*/
            foreach ($menus as $menu ){
                $subMenus=$menu->getMenus();
                $items[$menu->getNombre()]['menuraiz']=$menu->getId();
                if(count($subMenus)){
                    $items[$menu->getNombre()]['raiz1']='has-treeview';
                    $items[$menu->getNombre()]['raiz2']='<i class="fas fa-angle-left right"></i>';
                }else{
                    $items[$menu->getNombre()]['raiz1']='';
                    $items[$menu->getNombre()]['raiz2']='';
                }
                $titulo=$menu->getNombre();
                if(!is_null($menu->getModulo())){
                    $moduloPer=$moduloPerRepository->findOneBy(['empresa'=>1,'modulo'=>$menu->getModulo()->getId()]);
                    if(!is_null($moduloPer)){
                        $titulo=$moduloPer->getNombre();
                    }
                }
                $items[$menu->getNombre()]['title'] = $titulo;
                $items[$menu->getNombre()]['icon']=$menu->getIcono();
                if(is_null($menu->getModulo())){
                    $items[$menu->getNombre()]['url']="#";
                    $items[$menu->getNombre()]['class']="";
                }else{
                    $items[$menu->getNombre()]['url'] = $this->generateUrl($menu->getModulo()->getRuta());
                    $nombre_modulo=$menu->getModulo()->getNombre();
                    
                    if(in_array($route_name, [$nombre_modulo,$nombre_modulo.'_index', $nombre_modulo.'_show', $nombre_modulo.'_new', $nombre_modulo.'_edit', $nombre_modulo.'_regenera']))
                    {
                        $items[$menu->getNombre()]['class'] = "active";
                    }
                }

                /*
                * llenamos los submenus
                */
                $subMenus=$menuRepository->findBy(['dependeDe'=>$menu->getId(),'menuCabezera'=>$menuCabezera->getId()],['orden'=>'ASC']);
                foreach($subMenus as $submenu){
                    $titulo=$submenu->getNombre();
                    if(!is_null($submenu->getModulo())){
                        $moduloPer=$moduloPerRepository->findOneBy(['empresa'=>1,'modulo'=>$submenu->getModulo()->getId()]);
                        if(!is_null($moduloPer)){
                            $titulo=$moduloPer->getNombre();
                        }
                    }
                    $subitems[$submenu->getNombre()]['title'] = $titulo;
                    $subitems[$submenu->getNombre()]['icon']=$submenu->getIcono();
                    if(is_null($submenu->getModulo())){
                        $subitems[$submenu->getNombre()]['url']="#";
                        $subitems[$submenu->getNombre()]['class']="";
                    }else{
                       
                        $subitems[$submenu->getNombre()]['url'] = $this->generateUrl($submenu->getModulo()->getRuta());
                        $nombre_modulo=$submenu->getModulo()->getNombre();
                        
                        if(in_array($route_name, [$nombre_modulo,$nombre_modulo.'_index', $nombre_modulo.'_show', $nombre_modulo.'_new', $nombre_modulo.'_edit']))
                        {
                            $subitems[$submenu->getNombre()]['class'] = "active";
                        }
                    }
                    $subitems[$submenu->getNombre()]['menuraiz']=$menu->getId();
                }


            }
        }

        return $this->render('menu/_main.html.twig', [
            'items' => $items,
            'subitems'=>$subitems,
        ]);
    }

   

    #[Route("/{id}", name: "menu_index", methods: ["GET","POST"])]
    public function index(Request $request, MenuCabezera $menuCabezera, MenuRepository $menuRepository): Response
    {
        $this->denyAccessUnlessGranted('view','menu');
        $user=$this->getUser();

        return $this->render('menu/index.html.twig', [
            'menus' => $menuRepository->findBy(['dependeDe'=>null, 'menuCabezera'=>$menuCabezera->getId(),'empresa'=>1],['orden'=>'ASC']),
            'pagina'=>"Menu",
            'menuCabezera'=>$menuCabezera
        ]);
    }
    #[Route("/{id}/new", name: "menu_new", methods: ["GET","POST"])]
    public function new(Request $request, 
                        MenuCabezera $menuCabezera, 
                        MenuRepository $menuRepository,
                        EmpresaRepository $empresaRepository,
                        ModuloPerRepository $moduloPerRepository): Response
    {
        $this->denyAccessUnlessGranted('create','menu');
        $user=$this->getUser();
        $empresa=$empresaRepository->find($user->getEmpresaActual());
        $padres=$menus=$menuRepository->findBy(['dependeDe'=>null,'menuCabezera'=>$menuCabezera->getId(),'empresa'=>1],['orden'=>'ASC']);
        $modulos=$moduloPerRepository->findBy(['empresa'=>1],['nombre'=>'ASC']);
        $menu = new Menu();
        $menu->setEmpresa($empresa);
        $menu->setMenuCabezera($menuCabezera);
        $form = $this->createForm(MenuType::class, $menu);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $padre=$request->request->get('cboPadre');
    
            $menu->setDependeDe($menuRepository->find($padre));

           
            $modulo=$moduloPerRepository->find($request->request->get('cboModulo'));
            if(null !== $modulo)
                $menu->setModulo($modulo->getModulo());
            $entityManager = $this->entityManager;
            $entityManager->persist($menu);
            $entityManager->flush();
    
            return $this->redirectToRoute('menu_index',['id'=>$menuCabezera->getId()]);
        }

        return $this->render('menu/new.html.twig', [
            'menu' => $menu,
            'form' => $form->createView(),
            'menuCabezera'=>$menuCabezera,
            'padres'=>$padres,
            'modulos'=>$modulos,
        ]);
    }

    #[Route("/{id}/edit", name: "menu_edit", methods: ["GET","POST"])]
    public function edit(Request $request, Menu $menu,MenuRepository $menuRepository,ModuloPerRepository $moduloPerRepository): Response
    {
        $this->denyAccessUnlessGranted('edit','menu');
        $user=$this->getUser();
        $menuCabezera=$menu->getMenuCabezera();
        $form = $this->createForm(MenuType::class, $menu);
        $padres=$menuRepository->findBy(['dependeDe'=>null,'menuCabezera'=>$menuCabezera->getId()],['orden'=>'ASC']);
        $modulos=$moduloPerRepository->findBy(['empresa'=>$user->getEmpresaActual()],['nombre'=>'ASC']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $this->entityManager->flush();

            $padre=$request->request->get('cboPadre');
            $menu->setDependeDe($menuRepository->find($padre));
            $modulo=$moduloPerRepository->find($request->request->get('cboModulo'));
            if(null !== $modulo)
                $menu->setModulo($modulo->getModulo());
            $entityManager = $this->entityManager;
            $entityManager->persist($menu);
            $entityManager->flush();

            return $this->redirectToRoute('menu_index',['id'=>$menuCabezera->getId()]);
        }

        return $this->render('menu/edit.html.twig', [
            'menu' => $menu,
            'form' => $form->createView(),
            'menuCabezera'=>$menuCabezera,
            'padres'=>$padres,
            'modulos'=>$modulos,
        ]);
    }

    #[Route("/{id}", name: "menu_delete", methods: ["DELETE"])]
    public function delete(Request $request, Menu $menu): Response
    {
        $this->denyAccessUnlessGranted('full','menu');
        $menuCabezera=$menu->getMenuCabezera();
        if ($this->isCsrfTokenValid('delete'.$menu->getId(), $request->request->get('_token'))) {
            $entityManager = $this->entityManager;
            $entityManager->remove($menu);
            $entityManager->flush();
        }

        return $this->redirectToRoute('menu_index',['id'=>$menuCabezera->getId()]);
    }

    

}
