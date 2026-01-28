<?php

namespace App\DataFixtures;

use App\Entity\Artisan;
use App\Entity\Cooperative;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProductFixture extends Fixture implements DependentFixtureInterface
{
    public const PRODUCT_REFERENCE = 'product_%s';

    private array $productsData = [
        [
            'titre' => 'Bracelet en Argent Ciselé',
            'description' => 'Magnifique bracelet en argent massif avec ciselure traditionnelle marocaine. Pièce unique créée par un maître orfèvre avec 25 ans d\'expérience. Design géométrique inspiré des motifs berbères.',
            'prix' => '450.00',
            'stock' => 5,
            'dimensions' => '18cm x 2cm',
            'materiaux' => ['Argent 925', 'Or 18 carats'],
            'isActive' => true,
            'cooperative' => 0,
            'artisan' => 0
        ],
        [
            'titre' => 'Tapis Traditionnel Tissé Main',
            'description' => 'Tapis tissé main avec laine de qualité premium. Motifs géométriques traditionnels berbères en teintes naturelles. Dimensions 2m x 3m. Pièce authentique réalisée selon les techniques ancestrales.',
            'prix' => '1200.00',
            'stock' => 3,
            'dimensions' => '200cm x 300cm',
            'materiaux' => ['Laine Naturelle', 'Teinture Naturelle'],
            'isActive' => true,
            'cooperative' => 4,
            'artisan' => 1
        ],
        [
            'titre' => 'Table Basse en Bois de Cèdre',
            'description' => 'Table basse artisanale en bois de cèdre massif. Sculpture traditionnelle avec motifs géométriques. Dimensions 80x50cm. Vernis naturel. Pièce unique.',
            'prix' => '850.00',
            'stock' => 2,
            'dimensions' => '80cm x 50cm x 40cm',
            'materiaux' => ['Bois de Cèdre', 'Vernis Naturel'],
            'isActive' => true,
            'cooperative' => 2,
            'artisan' => 2
        ],
        [
            'titre' => 'Vase en Céramique Émaillée',
            'description' => 'Vase artisanal en céramique avec émaillage bleu cobalt traditionnel. Hauteur 30cm. Design élégant et fonctionnel. Idéal pour la décoration intérieure.',
            'prix' => '280.00',
            'stock' => 8,
            'dimensions' => '30cm hauteur x 15cm diamètre',
            'materiaux' => ['Céramique', 'Émaillage Traditionnel'],
            'isActive' => true,
            'cooperative' => 1,
            'artisan' => 3
        ],
        [
            'titre' => 'Ceinture en Cuir Tanné Naturellement',
            'description' => 'Ceinture en cuir véritable tanné naturellement sans produits chimiques. Boucle en laiton travaillé. Motifs gravés traditionnels. Longueur ajustable.',
            'prix' => '120.00',
            'stock' => 12,
            'dimensions' => 'Ajustable jusqu\'à 120cm',
            'materiaux' => ['Cuir Naturel', 'Laiton'],
            'isActive' => true,
            'cooperative' => 3,
            'artisan' => 4
        ],
        [
            'titre' => 'Coran Illuminé en Calligraphie',
            'description' => 'Coran magnifiquement calligraphié et enluminé à la main. Encres naturelles sur papier premium. Reliure en cuir. Pièce de collection. Dimensions 25x35cm.',
            'prix' => '2500.00',
            'stock' => 1,
            'dimensions' => '25cm x 35cm',
            'materiaux' => ['Papier Premium', 'Encres Naturelles', 'Cuir'],
            'isActive' => true,
            'cooperative' => 0,
            'artisan' => 5
        ],
        [
            'titre' => 'Vase en Verre Soufflé Bleu',
            'description' => 'Vase créé par la technique du verre soufflé traditionnel. Couleur bleu profond avec motifs intégrés. Hauteur 35cm. Pièce unique non reproductible.',
            'prix' => '380.00',
            'stock' => 4,
            'dimensions' => '35cm hauteur x 20cm diamètre',
            'materiaux' => ['Verre Soufflé'],
            'isActive' => true,
            'cooperative' => 2,
            'artisan' => 6
        ],
        [
            'titre' => 'Babouches Brodées en Velours',
            'description' => 'Babouches traditionnelles marocaines en velours doux avec broderie dorée. Semelle cuir souple. Confortables et décoratives. Disponible en plusieurs tailles.',
            'prix' => '95.00',
            'stock' => 20,
            'dimensions' => 'Du 35 au 44',
            'materiaux' => ['Velours', 'Cuir', 'Fil Doré'],
            'isActive' => true,
            'cooperative' => 1,
            'artisan' => 7
        ],
        [
            'titre' => 'Panneau Zellige Décoratif',
            'description' => 'Panneau décoratif en zellige traditionnel. Carreaux de céramique émaillée assemblés en motif géométrique. Dimensions 60x60cm. Prêt à accrocher.',
            'prix' => '450.00',
            'stock' => 3,
            'dimensions' => '60cm x 60cm',
            'materiaux' => ['Céramique', 'Émaillage Zellige'],
            'isActive' => true,
            'cooperative' => 3,
            'artisan' => 8
        ],
        [
            'titre' => 'Coussin Brodé Traditionnel',
            'description' => 'Coussin carré en soie avec broderie main de motifs géométriques berbères. Remplissage en duvet. Dimensions 40x40cm. Chaque pièce est unique.',
            'prix' => '175.00',
            'stock' => 15,
            'dimensions' => '40cm x 40cm',
            'materiaux' => ['Soie', 'Duvet Premium', 'Fil Broderie'],
            'isActive' => true,
            'cooperative' => 4,
            'artisan' => 9
        ],
        [
            'titre' => 'Porte en Fer Forgé Traditionnel',
            'description' => 'Porte décorative artisanale en fer forgé. Motifs floraux complexes travaillés à la main. Dimensions 120x200cm. Installation possible.',
            'prix' => '1500.00',
            'stock' => 1,
            'dimensions' => '120cm x 200cm',
            'materiaux' => ['Fer Forgé', 'Peinture Antirouille'],
            'isActive' => false,
            'cooperative' => 0,
            'artisan' => 10
        ],
        [
            'titre' => 'Plateau en Cuivre Repoussé',
            'description' => 'Plateau rond en cuivre travaillé par la technique du repoussé. Motifs géométriques et floraux. Pieds en bois. Diamètre 60cm.',
            'prix' => '320.00',
            'stock' => 6,
            'dimensions' => '60cm diamètre',
            'materiaux' => ['Cuivre Repoussé', 'Bois'],
            'isActive' => true,
            'cooperative' => 1,
            'artisan' => 0
        ],
        [
            'titre' => 'Sac Bereber en Laine Tissée',
            'description' => 'Sac traditionnel berbère tissé main en laine colorée. Motifs géométriques authentiques. Lanières en cuir. Parfait pour la décoration ou l\'utilisation quotidienne.',
            'prix' => '95.00',
            'stock' => 18,
            'dimensions' => '40cm x 30cm',
            'materiaux' => ['Laine', 'Cuir', 'Coton'],
            'isActive' => true,
            'cooperative' => 4,
            'artisan' => 1
        ]
    ];

    public function load(ObjectManager $manager): void
    {
        foreach ($this->productsData as $index => $data) {
            $product = new Product();
            $product->setTitre($data['titre']);
            $product->setDescription($data['description']);
            $product->setPrix($data['prix']);
            $product->setStock($data['stock']);
            $product->setDimensions($data['dimensions']);
            $product->setMateriaux($data['materiaux']);
            $product->setIsActive($data['isActive']);
            $product->setSlug($this->slugify($data['titre']));
            $product->setCooperative($this->getReference(\sprintf(CooperativeFixture::COOPERATIVE_REFERENCE, $data['cooperative']), Cooperative::class));
            $product->addArtisan($this->getReference(\sprintf(ArtisanFixture::ARTISAN_REFERENCE, $data['artisan']), Artisan::class));
            $product->setCreatedAt(new \DateTimeImmutable());
            $product->setUpdatedAt(new \DateTime());

            $manager->persist($product);
            $this->addReference(\sprintf(self::PRODUCT_REFERENCE, $index), $product);
        }

        $manager->flush();
    }

    private function slugify(string $text): string
    {
        $text = mb_strtolower($text);
        $text = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        return trim($text, '-');
    }

    public function getDependencies(): array
    {
        return [
            CooperativeFixture::class,
            ArtisanFixture::class,
        ];
    }
}
