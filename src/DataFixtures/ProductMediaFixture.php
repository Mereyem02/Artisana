<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\ProductMedia;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProductMediaFixture extends Fixture implements DependentFixtureInterface
{
    /**
     * URLs de différentes APIs de photos d'humains et de produits
     * - Picsum Photos: https://picsum.photos (photos aléatoires de qualité)
     * - Unsplash: https://source.unsplash.com (API simple)
     * - Pexels: Utilise des IDs spécifiques
     * - LoremPicsum: https://picsum.photos
     */
    private array $imageUrls = [
        // Images de produits d'artisanat
        'https://picsum.photos/600/400?random=100', // Bijoux
        'https://picsum.photos/600/400?random=101', // Tapis
        'https://picsum.photos/600/400?random=102', // Bois
        'https://picsum.photos/600/400?random=103', // Céramique
        'https://picsum.photos/600/400?random=104', // Cuir
        'https://picsum.photos/600/400?random=105', // Art
        'https://picsum.photos/600/400?random=106', // Verre
        'https://picsum.photos/600/400?random=107', // Chaussures
        'https://picsum.photos/600/400?random=108', // Carrelage
        'https://picsum.photos/600/400?random=109', // Textile
        'https://picsum.photos/600/400?random=110', // Métal
        'https://picsum.photos/600/400?random=111', // Accessoires
    ];

    private array $productMediaData = [
        // Bracelet en Argent Ciselé - Product 0
        [
            'product' => 0,
            'images' => [
                [
                    'filename' => 'https://picsum.photos/800/600?random=100',
                    'type' => 'image/jpeg',
                    'caption' => 'Bracelet en argent - Vue principale',
                    'order' => 1,
                    'url' => 'https://picsum.photos/800/600?random=100'
                ],
                [
                    'filename' => 'https://picsum.photos/800/600?random=101',
                    'type' => 'image/jpeg',
                    'caption' => 'Détail de la ciselure',
                    'order' => 2,
                    'url' => 'https://picsum.photos/800/600?random=101'
                ],
                [
                    'filename' => 'https://picsum.photos/800/600?random=102',
                    'type' => 'image/jpeg',
                    'caption' => 'Bracelet porté',
                    'order' => 3,
                    'url' => 'https://picsum.photos/800/600?random=102'
                ]
            ]
        ],
        // Tapis Traditionnel - Product 1
        [
            'product' => 1,
            'images' => [
                [
                    'filename' => 'https://picsum.photos/800/600?random=103',
                    'type' => 'image/jpeg',
                    'caption' => 'Tapis tissé main - Vue d\'ensemble',
                    'order' => 1,
                    'url' => 'https://picsum.photos/800/600?random=103'
                ],
                [
                    'filename' => 'https://picsum.photos/800/600?random=104',
                    'type' => 'image/jpeg',
                    'caption' => 'Motifs géométriques du tapis',
                    'order' => 2,
                    'url' => 'https://picsum.photos/800/600?random=104'
                ],
                [
                    'filename' => 'https://picsum.photos/800/600?random=105',
                    'type' => 'image/jpeg',
                    'caption' => 'Texture de la laine',
                    'order' => 3,
                    'url' => 'https://picsum.photos/800/600?random=105'
                ]
            ]
        ],
        // Table en Bois de Cèdre - Product 2
        [
            'product' => 2,
            'images' => [
                [
                    'filename' => 'https://picsum.photos/800/600?random=106',
                    'type' => 'image/jpeg',
                    'caption' => 'Table basse en bois de cèdre',
                    'order' => 1,
                    'url' => 'https://picsum.photos/800/600?random=106'
                ],
                [
                    'filename' => 'https://picsum.photos/800/600?random=107',
                    'type' => 'image/jpeg',
                    'caption' => 'Sculpture et motifs détaillés',
                    'order' => 2,
                    'url' => 'https://picsum.photos/800/600?random=107'
                ],
                [
                    'filename' => 'https://picsum.photos/800/600?random=108',
                    'type' => 'image/jpeg',
                    'caption' => 'Vue de côté de la table',
                    'order' => 3,
                    'url' => 'https://picsum.photos/800/600?random=108'
                ]
            ]
        ],
        // Vase Céramique - Product 3
        [
            'product' => 3,
            'images' => [
                [
                    'filename' => 'https://picsum.photos/800/600?random=109',
                    'type' => 'image/jpeg',
                    'caption' => 'Vase en céramique émaillée',
                    'order' => 1,
                    'url' => 'https://picsum.photos/800/600?random=109'
                ],
                [
                    'filename' => 'https://picsum.photos/800/600?random=110',
                    'type' => 'image/jpeg',
                    'caption' => 'Détail de l\'émaillage bleu',
                    'order' => 2,
                    'url' => 'https://picsum.photos/800/600?random=110'
                ]
            ]
        ],
        // Ceinture Cuir - Product 4
        [
            'product' => 4,
            'images' => [
                [
                    'filename' => 'https://picsum.photos/800/600?random=111',
                    'type' => 'image/jpeg',
                    'caption' => 'Ceinture en cuir tanné naturellement',
                    'order' => 1,
                    'url' => 'https://picsum.photos/800/600?random=111'
                ],
                [
                    'filename' => 'https://picsum.photos/800/600?random=112',
                    'type' => 'image/jpeg',
                    'caption' => 'Détail de la boucle en laiton',
                    'order' => 2,
                    'url' => 'https://picsum.photos/800/600?random=112'
                ],
                [
                    'filename' => 'https://picsum.photos/800/600?random=113',
                    'type' => 'image/jpeg',
                    'caption' => 'Ceinture portée',
                    'order' => 3,
                    'url' => 'https://picsum.photos/800/600?random=113'
                ]
            ]
        ],
        // Coran Calligraphié - Product 5
        [
            'product' => 5,
            'images' => [
                [
                    'filename' => 'https://picsum.photos/800/600?random=114',
                    'type' => 'image/jpeg',
                    'caption' => 'Coran illuminé - Couverture',
                    'order' => 1,
                    'url' => 'https://picsum.photos/800/600?random=114'
                ],
                [
                    'filename' => 'https://picsum.photos/800/600?random=115',
                    'type' => 'image/jpeg',
                    'caption' => 'Page intérieure avec calligraphie',
                    'order' => 2,
                    'url' => 'https://picsum.photos/800/600?random=115'
                ],
                [
                    'filename' => 'https://picsum.photos/800/600?random=116',
                    'type' => 'image/jpeg',
                    'caption' => 'Détail de l\'enluminure',
                    'order' => 3,
                    'url' => 'https://picsum.photos/800/600?random=116'
                ]
            ]
        ],
        // Vase Verre Soufflé - Product 6
        [
            'product' => 6,
            'images' => [
                [
                    'filename' => 'https://picsum.photos/800/600?random=117',
                    'type' => 'image/jpeg',
                    'caption' => 'Vase en verre soufflé bleu',
                    'order' => 1,
                    'url' => 'https://picsum.photos/800/600?random=117'
                ],
                [
                    'filename' => 'https://picsum.photos/800/600?random=118',
                    'type' => 'image/jpeg',
                    'caption' => 'Vue de face du vase',
                    'order' => 2,
                    'url' => 'https://picsum.photos/800/600?random=118'
                ]
            ]
        ],
        // Babouches - Product 7
        [
            'product' => 7,
            'images' => [
                [
                    'filename' => 'https://picsum.photos/800/600?random=119',
                    'type' => 'image/jpeg',
                    'caption' => 'Babouches brodées - Vue d\'ensemble',
                    'order' => 1,
                    'url' => 'https://picsum.photos/800/600?random=119'
                ],
                [
                    'filename' => 'https://picsum.photos/800/600?random=120',
                    'type' => 'image/jpeg',
                    'caption' => 'Broderie dorée détail',
                    'order' => 2,
                    'url' => 'https://picsum.photos/800/600?random=120'
                ],
                [
                    'filename' => 'https://picsum.photos/800/600?random=121',
                    'type' => 'image/jpeg',
                    'caption' => 'Babouches portées',
                    'order' => 3,
                    'url' => 'https://picsum.photos/800/600?random=121'
                ]
            ]
        ],
        // Panneau Zellige - Product 8
        [
            'product' => 8,
            'images' => [
                [
                    'filename' => 'https://picsum.photos/800/600?random=122',
                    'type' => 'image/jpeg',
                    'caption' => 'Panneau zellige décoratif',
                    'order' => 1,
                    'url' => 'https://picsum.photos/800/600?random=122'
                ],
                [
                    'filename' => 'https://picsum.photos/800/600?random=123',
                    'type' => 'image/jpeg',
                    'caption' => 'Détail des carreaux',
                    'order' => 2,
                    'url' => 'https://picsum.photos/800/600?random=123'
                ]
            ]
        ],
        // Coussin Brodé - Product 9
        [
            'product' => 9,
            'images' => [
                [
                    'filename' => 'https://picsum.photos/800/600?random=124',
                    'type' => 'image/jpeg',
                    'caption' => 'Coussin brodé traditionnel',
                    'order' => 1,
                    'url' => 'https://picsum.photos/800/600?random=124'
                ],
                [
                    'filename' => 'https://picsum.photos/800/600?random=125',
                    'type' => 'image/jpeg',
                    'caption' => 'Motifs broderie détail',
                    'order' => 2,
                    'url' => 'https://picsum.photos/800/600?random=125'
                ]
            ]
        ],
        // Porte Fer Forgé - Product 10
        [
            'product' => 10,
            'images' => [
                [
                    'filename' => 'https://picsum.photos/800/600?random=126',
                    'type' => 'image/jpeg',
                    'caption' => 'Porte en fer forgé traditionnel',
                    'order' => 1,
                    'url' => 'https://picsum.photos/800/600?random=126'
                ],
                [
                    'filename' => 'https://picsum.photos/800/600?random=127',
                    'type' => 'image/jpeg',
                    'caption' => 'Motifs floraux détail',
                    'order' => 2,
                    'url' => 'https://picsum.photos/800/600?random=127'
                ]
            ]
        ],
        // Plateau Cuivre - Product 11
        [
            'product' => 11,
            'images' => [
                [
                    'filename' => 'https://picsum.photos/800/600?random=128',
                    'type' => 'image/jpeg',
                    'caption' => 'Plateau en cuivre repoussé',
                    'order' => 1,
                    'url' => 'https://picsum.photos/800/600?random=128'
                ],
                [
                    'filename' => 'https://picsum.photos/800/600?random=129',
                    'type' => 'image/jpeg',
                    'caption' => 'Motifs repoussés',
                    'order' => 2,
                    'url' => 'https://picsum.photos/800/600?random=129'
                ]
            ]
        ],
        // Sac Berbère - Product 12
        [
            'product' => 12,
            'images' => [
                [
                    'filename' => 'https://picsum.photos/800/600?random=130',
                    'type' => 'image/jpeg',
                    'caption' => 'Sac berbère en laine tissée',
                    'order' => 1,
                    'url' => 'https://picsum.photos/800/600?random=130'
                ],
                [
                    'filename' => 'https://picsum.photos/800/600?random=131',
                    'type' => 'image/jpeg',
                    'caption' => 'Motifs géométriques',
                    'order' => 2,
                    'url' => 'https://picsum.photos/800/600?random=131'
                ],
                [
                    'filename' => 'https://picsum.photos/800/600?random=132',
                    'type' => 'image/jpeg',
                    'caption' => 'Sac porté',
                    'order' => 3,
                    'url' => 'https://picsum.photos/800/600?random=132'
                ]
            ]
        ]
    ];

    public function load(ObjectManager $manager): void
    {
        foreach ($this->productMediaData as $productData) {
            $product = $this->getReference(\sprintf(ProductFixture::PRODUCT_REFERENCE, $productData['product']), Product::class);
            
            foreach ($productData['images'] as $image) {
                $media = new ProductMedia();
                $media->setFilename($image['filename']);
                $media->setType($image['type']);
                $media->setCaption($image['caption']);
                $media->setOrderIt($image['order']);
                $media->setProduct($product);
                $media->setUpdatedAt(new \DateTime());

                // Stockage de l'URL pour utilisation ultérieure
                // Cette URL peut être utilisée pour télécharger l'image réellement
                // dans un processus de migration ultérieur
                
                $manager->persist($media);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ProductFixture::class,
        ];
    }
}
