<?php

namespace App\DataFixtures;

use App\Entity\Artisan;
use App\Entity\Cooperative;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ArtisanFixture extends Fixture implements DependentFixtureInterface
{
    public const ARTISAN_REFERENCE = 'artisan_%s';

    private array $artisansData = [
        [
            'nom' => 'Hassan Boutalib',
            'email' => 'hassan.boutalib@artisan.ma',
            'telephone' => '+212 6 61 23 45 67',
            'bio' => 'Maître orfèvre avec 25 ans d\'expérience. Spécialisé dans la création de bijoux traditionnels en argent et or avec des techniques ancestrales marocaines.',
            'photo' => 'https://i.pravatar.cc/400?img=1',
            'competences' => ['Orfèvrerie', 'Bijouterie', 'Design', 'Restauration'],
            'verified' => true,
            'approvalStatus' => 'APPROVED',
            'cooperative' => 0
        ],
        [
            'nom' => 'Fatima El Hassani',
            'email' => 'fatima.hassani@artisan.ma',
            'telephone' => '+212 6 71 34 56 78',
            'bio' => 'Tisserande passionnée créant des tapis et textiles traditionnels. Experte en teinture naturelle et motifs géométriques authentiques.',
            'photo' => 'https://i.pravatar.cc/400?img=5',
            'competences' => ['Tissage', 'Teinture Naturelle', 'Broderie', 'Design Textile'],
            'verified' => true,
            'approvalStatus' => 'APPROVED',
            'cooperative' => 4
        ],
        [
            'nom' => 'Mohamed Najah',
            'email' => 'mohamed.najah@artisan.ma',
            'telephone' => '+212 6 81 45 67 89',
            'bio' => 'Charpentier traditionnel spécialisé dans la menuiserie en bois de cèdre et thuya. Créateur de meubles et objets décorativement sculptés.',
            'photo' => 'https://i.pravatar.cc/400?img=12',
            'competences' => ['Menuiserie', 'Sculpture Bois', 'Restauration', 'Design Mobilier'],
            'verified' => true,
            'approvalStatus' => 'APPROVED',
            'cooperative' => 2
        ],
        [
            'nom' => 'Aicha Bencherif',
            'email' => 'aicha.bencherif@artisan.ma',
            'telephone' => '+212 6 91 56 78 90',
            'bio' => 'Céramiste reconnue travaillant avec des techniques poterie traditionnelles. Créatrice de pièces d\'art en terre cuite avec finitions émaillées.',
            'photo' => 'https://i.pravatar.cc/400?img=9',
            'competences' => ['Céramique', 'Poterie', 'Émaillage', 'Art Contemporain'],
            'verified' => true,
            'approvalStatus' => 'APPROVED',
            'cooperative' => 1
        ],
        [
            'nom' => 'Rachid Alami',
            'email' => 'rachid.alami@artisan.ma',
            'telephone' => '+212 6 62 67 89 01',
            'bio' => 'Artisan du cuir avec 15 ans d\'expérience. Fabricant de ceintures, sacs et accessoires en cuir tanné naturellement.',
            'photo' => 'https://i.pravatar.cc/400?img=15',
            'competences' => ['Travail du Cuir', 'Tannage', 'Maroquinerie', 'Design Accessoires'],
            'verified' => true,
            'approvalStatus' => 'APPROVED',
            'cooperative' => 3
        ],
        [
            'nom' => 'Samira Khoury',
            'email' => 'samira.khoury@artisan.ma',
            'telephone' => '+212 6 72 78 89 01',
            'bio' => 'Calligraphe et enlumineuse spécialisée dans l\'art du manuscrit et du Coran. Crée des œuvres originales en encres naturelles.',
            'photo' => 'https://i.pravatar.cc/400?img=8',
            'competences' => ['Calligraphie', 'Enluminure', 'Art Manuscrit', 'Restauration Livres'],
            'verified' => true,
            'approvalStatus' => 'APPROVED',
            'cooperative' => 0
        ],
        [
            'nom' => 'Karim Ben Salim',
            'email' => 'karim.bensalim@artisan.ma',
            'telephone' => '+212 6 82 89 90 12',
            'bio' => 'Maître verrier créant des vases et objets en verre soufflé. Expert en techniques de fusion et coloration du verre.',
            'photo' => 'https://i.pravatar.cc/400?img=18',
            'competences' => ['Verre Soufflé', 'Fusion Verre', 'Design Verre', 'Technique Ancienne'],
            'verified' => false,
            'approvalStatus' => 'PENDING',
            'cooperative' => 2
        ],
        [
            'nom' => 'Leila Bennani',
            'email' => 'leila.bennani@artisan.ma',
            'telephone' => '+212 6 92 90 01 23',
            'bio' => 'Créatrice de babouches et chaussures traditionnelles marocaines. Maîtrise complète des techniques de couture et teinture.',
            'photo' => 'https://i.pravatar.cc/400?img=6',
            'competences' => ['Babouches', 'Chaussures', 'Couture', 'Teinture'],
            'verified' => true,
            'approvalStatus' => 'APPROVED',
            'cooperative' => 1
        ],
        [
            'nom' => 'Omar Chrifi',
            'email' => 'omar.chrifi@artisan.ma',
            'telephone' => '+212 6 61 01 23 45',
            'bio' => 'Artisan des zellige et mosaïques. Crée des panneaux décoratifs avec les techniques traditionnelles de carrelage.',
            'photo' => 'https://i.pravatar.cc/400?img=20',
            'competences' => ['Zellige', 'Mosaïque', 'Carrelage', 'Design Intérieur'],
            'verified' => true,
            'approvalStatus' => 'APPROVED',
            'cooperative' => 3
        ],
        [
            'nom' => 'Noor Al-Rashid',
            'email' => 'noor.rashid@artisan.ma',
            'telephone' => '+212 6 71 23 45 67',
            'bio' => 'Artiste dans le domaine de la broderie et des textiles. Crée des pièces uniques avec des motifs traditionnels et contemporains.',
            'photo' => 'https://i.pravatar.cc/400?img=11',
            'competences' => ['Broderie', 'Textile', 'Design Motifs', 'Couture Artisanale'],
            'verified' => true,
            'approvalStatus' => 'APPROVED',
            'cooperative' => 4
        ],
        [
            'nom' => 'Adnan Fassi',
            'email' => 'adnan.fassi@artisan.ma',
            'telephone' => '+212 6 81 34 56 78',
            'bio' => 'Ferronnerie d\'art traditionnel. Créateur de portes, grilles et objets décorés en fer forgé selon les techniques ancestrales.',
            'photo' => 'https://i.pravatar.cc/400?img=22',
            'competences' => ['Fer Forgé', 'Ferronnerie', 'Sculpture Métal', 'Design Architectural'],
            'verified' => false,
            'approvalStatus' => 'PENDING',
            'cooperative' => 0
        ]
    ];

    public function load(ObjectManager $manager): void
    {
        foreach ($this->artisansData as $index => $data) {
            $artisan = new Artisan();
            $artisan->setNom($data['nom']);
            $artisan->setEmail($data['email']);
            $artisan->setTelephone($data['telephone']);
            $artisan->setBio($data['bio']);
            $artisan->setPhoto($data['photo']);
            $artisan->setCompetences($data['competences']);
            $artisan->setVerified($data['verified']);
            $artisan->setApprovalStatus($data['approvalStatus']);
            $artisan->setCooperative($this->getReference(sprintf(CooperativeFixture::COOPERATIVE_REFERENCE, $data['cooperative']), Cooperative::class));
            $artisan->setCreatedAt(new \DateTimeImmutable());
            $artisan->setUpdatedAt(new \DateTime());

            $manager->persist($artisan);
            $this->addReference(sprintf(self::ARTISAN_REFERENCE, $index), $artisan);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CooperativeFixture::class,
        ];
    }
}
