<?php

namespace App\Form;

use App\Entity\Cliente;
use App\Entity\Contrato;
use App\Entity\PlanPrepago;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class ContratoType extends AbstractType
{
    public function __construct(private readonly Security $security)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    { 
        $cliente = $options['cliente'];

        $builder
            ->add('nombre', TextType::class, [
                'mapped' => false,
                'data' => $cliente ? $cliente->getNombre() : null,
            ])
            ->add('email', TextType::class, [
                'mapped' => false,
                'data' => $cliente ? $cliente->getCorreo() : null,
                'constraints' => CorreoClienteConstraints::restricciones(),
            ])
            ->add('telefono', TextType::class, [
                'mapped' => false,
                'data' => $cliente ? $cliente->getTelefono() : null,
            ])
            ->add('rut', TextType::class, [
                'mapped' => false,
                'data' => $cliente ? $cliente->getRut() : null,
            ])
            ->add('direccion', TextType::class, [
                'mapped' => false,
                'data' => $cliente ? $cliente->getDireccion() : null,
            ])
            ->add('montoNivelDeuda')
            ->add('MontoContrato')
            ->add('primeraCuota')
            ->add('isAbono')
            ->add('isTotal')
            ->add('isProBono')
            ->add('isPrePago')
            // Planes de pre-pago activos de la empresa del usuario (mantenedor plan_prepago).
            ->add('planPrepago', EntityType::class, [
                'class' => PlanPrepago::class,
                'required' => false,
                'placeholder' => 'Seleccione plan',
                'query_builder' => function (EntityRepository $er) {
                    $user = $this->security->getUser();
                    return $er->createQueryBuilder('p')
                        ->andWhere('p.estado = true')
                        ->andWhere('p.empresa = :empresa')
                        ->setParameter('empresa', $user ? $user->getEmpresaActual() : 0)
                        ->orderBy('p.nombre', 'ASC');
                },
            ])
            ->add('cuotas')
            ->add('valorCuota')
            ->add('situacionLaboral')
             ->add('claveUnica', TextType::class, [
                'mapped' => false,
                'required' => false,
                'data' => $cliente ? $cliente->getClaveUnica() : null,
            ])
            ->add('pjudRut', TextType::class, [
                'mapped' => false,
                'required' => false,
                'data' => $cliente ? $cliente->getPjudRut() : null,
            ])
            ->add('pais')
            ->add('telefonoRecado', TextType::class, [
                'mapped' => false,
                'required' => false,
                'data' => $cliente ? $cliente->getTelefonoRecado() : null,
            ])
            ->add('vehiculo')
            ->add('vivienda')
            ->add('reunion')
            ->add('observacion')

        ;

        // Pre-pago exige plan; si el contrato no es pre-pago no debe quedar ningún plan asociado.
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $contrato = $event->getData();
            if (!$contrato instanceof Contrato) {
                return;
            }
            if ($contrato->getIsPrePago()) {
                if ($contrato->getPlanPrepago() === null) {
                    $event->getForm()->get('planPrepago')->addError(new FormError('Debe seleccionar el plan de pre-pago.'));
                }
            } elseif ($event->getForm()->has('planPrepago')) {
                $contrato->setPlanPrepago(null);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Contrato::class,
            'cliente' => null,
        ]);
        $resolver->setAllowedTypes('cliente', [Cliente::class, 'null']);
    }
}
