<?php

namespace App\DataFixtures;

use App\Entity\Cooperative;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CooperativeFixture extends Fixture
{
    public const COOPERATIVE_REFERENCE = 'cooperative_%s';

    private array $cooperativesData = [
        [
            'nom' => 'Chabi Chic',
            'adresse' => '123 Rue de l\'Artisanat, Fès',
            'ville' => 'Fès',
            'region' => 'Fès-Meknès',
            'description' => 'Coopérative regroupant les meilleurs artisans du Maroc, spécialisés dans l\'art traditionnel et l\'artisanat de qualité.',
            'email' => 'contact@cooperativeartisans.ma',
            'telephone' => '+212 5 35 65 00 00',
            'contact' => 'Mohammed Bennani',
            'status' => 'ACTIVE',
            'siteWeb' => 'www.cooperativeartisans.ma',
            'logo' => 'https://picsum.photos/400/300?random=1'
        ],
        [
            'nom' => 'Atelier Collectif Tanger',
            'adresse' => '456 Avenue Mohammed V, Tanger',
            'ville' => 'Tanger',
            'region' => 'Tanger-Tétouan-Al Hoceïma',
            'description' => 'Collectif d\'artisans talentueux produisant des articles de décoration et de mode inspirés par la culture marocaine.',
            'email' => 'info@ateliercolletiftanger.ma',
            'telephone' => '+212 5 39 94 25 50',
            'contact' => 'Fatima El Fassi',
            'status' => 'ACTIVE',
            'siteWeb' => 'www.ateliercolletiftanger.ma',
            'logo' => 'https://picsum.photos/400/300?random=2'
        ],
        [
            'nom' => 'Maison du Savoir-Faire',
            'adresse' => '789 Quartier Médina, Marrakech',
            'ville' => 'Marrakech',
            'region' => 'Marrakech-Safi',
            'description' => 'Dédiée à la préservation et la promotion des techniques artisanales traditionnelles du Maroc.',
            'email' => 'contact@maisonsavoirfaire.ma',
            'telephone' => '+212 5 24 43 80 00',
            'contact' => 'Ahmed Qadri',
            'status' => 'ACTIVE',
            'siteWeb' => 'www.maisonsavoirfaire.ma',
            'logo' => 'https://picsum.photos/400/300?random=3'
        ],
        [
            'nom' => 'Coopérative Mains d\'Argent',
            'adresse' => '321 Rue des Métiers, Essaouira',
            'ville' => 'Essaouira',
            'region' => 'Marrakech-Safi',
            'description' => 'Spécialisée dans l\'orfèvrerie et les articles en argent d\'excellente qualité.',
            'email' => 'contact@mains-argent.ma',
            'telephone' => '+212 5 24 47 30 40',
            'contact' => 'Zohra Bennani',
            'status' => 'ACTIVE',
            'siteWeb' => 'www.mains-argent.ma',
            'logo' => 'https://picsum.photos/400/300?random=4'
        ],
        [
            'nom' => 'Atelier Tapis et Tissus',
            'adresse' => '654 Medina, Meknès',
            'ville' => 'Meknès',
            'region' => 'Fès-Meknès',
            'description' => 'Production de tapis traditionnels et de tissus brodés avec des techniques ancestrales.',
            'email' => 'contact@tapis-tissus.ma',
            'telephone' => '+212 5 35 52 80 00',
            'contact' => 'Abdellah Chrif',
            'status' => 'ACTIVE',
            'siteWeb' => 'www.tapis-tissus.ma',
            'logo' => 'https://picsum.photos/400/300?random=5'
        ]
    ];

    public function load(ObjectManager $manager): void
    {
        foreach ($this->cooperativesData as $index => $data) {
            $cooperative = new Cooperative();
            $cooperative->setNom($data['nom']);
            $cooperative->setAdresse($data['adresse']);
            $cooperative->setVille($data['ville']);
            $cooperative->setRegion($data['region']);
            $cooperative->setDescription($data['description']);
            $cooperative->setEmail($data['email']);
            $cooperative->setTelephone($data['telephone']);
            $cooperative->setContact($data['contact']);
            $cooperative->setStatus($data['status']);
            $cooperative->setSiteWeb($data['siteWeb']);
            $cooperative->setLogo($data['logo']);
            $cooperative->setCreatedAt(new \DateTimeImmutable());
            $cooperative->setUpdatedAt(new \DateTime());

            $manager->persist($cooperative);
            $this->addReference(sprintf(self::COOPERATIVE_REFERENCE, $index), $cooperative);
        }

        $manager->flush();
    }
}
