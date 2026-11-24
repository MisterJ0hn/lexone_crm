<?php

namespace App\Form;

use App\Entity\Pago;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Entity\CuentaCorriente;
use App\Entity\PagoCanal;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class PagoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('monto')
            ->add('boleta')
            ->add('observacion')
            
            ->add('ncomprobante')
            ->add('pagoTipo')
            ->add('pagoCanal', EntityType::class, $this->opcionesCatalogo(PagoCanal::class, $options['empresa'], $builder->getData()?->getPagoCanal(), true))
            ->add('cuentaCorriente', EntityType::class, $this->opcionesCatalogo(CuentaCorriente::class, $options['empresa'], $builder->getData()?->getCuentaCorriente(), true))
            ->add('comprobante')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Pago::class,
            'empresa' => null,
        ]);
        $resolver->setAllowedTypes('empresa', ['null', 'int']);
    }

    /** Opciones de un catálogo por empresa (siempre incluye el valor ya elegido, aunque sea de otra empresa). */
    private function opcionesCatalogo(string $clase, ?int $empresa, ?object $actual, bool $obligatorio): array
    {
        return [
            'class' => $clase,
            'required' => $obligatorio,
            'query_builder' => function (EntityRepository $er) use ($empresa, $actual) {
                $qb = $er->createQueryBuilder('x')->orderBy('x.nombre', 'ASC');
                $cond = 'x.empresa = :empresa';
                if ($actual !== null && $actual->getId() !== null) {
                    $cond .= ' OR x.id = :actual';
                    $qb->setParameter('actual', $actual->getId());
                }
                return $qb->andWhere($cond)->setParameter('empresa', $empresa ?? 0);
            },
        ];
    }
}
