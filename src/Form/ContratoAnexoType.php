<?php

namespace App\Form;

use App\Entity\ContratoAnexo;
use App\Entity\PlanPrepago;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class ContratoAnexoType extends AbstractType
{
    public function __construct(private readonly Security $security)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('MontoContrato')
            ->add('isAbono')
            ->add('abono')
            ->add('isTotal')
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
                        ->orderBy('p.id', 'ASC');
                },
            ])
            ->add('nCuotas')
            ->add('valorCuota')
            ->add('vigencia')
            ->add('observacion')
        ;

        // Pre-pago exige plan y queda con 1 cuota y monto = valor cuota (no se confía en el cliente);
        // si el anexo no es pre-pago no debe quedar ningún plan asociado.
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $anexo = $event->getData();
            if (!$anexo instanceof ContratoAnexo) {
                return;
            }
            if ($anexo->getIsPrePago()) {
                if ($anexo->getPlanPrepago() === null) {
                    $event->getForm()->get('planPrepago')->addError(new FormError('Debe seleccionar el plan de pre-pago.'));
                }
                $anexo->setNCuotas(1);
                $anexo->setMontoContrato($anexo->getValorCuota());
            } else {
                $anexo->setPlanPrepago(null);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ContratoAnexo::class,
        ]);
    }
}
