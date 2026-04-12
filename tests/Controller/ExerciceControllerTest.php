<?php

namespace App\Tests\Controller;

use App\Entity\Exercice;
use App\Repository\ExerciceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ExerciceControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    /** @var EntityRepository<Exercice> $exerciceRepository */
    private EntityRepository $exerciceRepository;
    private string $path = '/exercice/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->exerciceRepository = $this->manager->getRepository(Exercice::class);

        foreach ($this->exerciceRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Exercice index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'exercice[titre]' => 'Testing',
            'exercice[type]' => 'Testing',
            'exercice[consigne]' => 'Testing',
            'exercice[difficulte]' => 'Testing',
            'exercice[duree]' => 'Testing',
            'exercice[contenu]' => 'Testing',
            'exercice[pour_tous_enfants]' => 'Testing',
            'exercice[actif]' => 'Testing',
            'exercice[cree_par]' => 'Testing',
            'exercice[date_creation]' => 'Testing',
            'exercice[complete]' => 'Testing',
            'exercice[archive]' => 'Testing',
        ]);

        self::assertResponseRedirects('/exercice');

        self::assertSame(1, $this->exerciceRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }

    public function testShow(): void
    {
        $fixture = new Exercice();
        $fixture->setTitre('My Title');
        $fixture->setType('My Title');
        $fixture->setConsigne('My Title');
        $fixture->setDifficulte('My Title');
        $fixture->setDuree('My Title');
        $fixture->setContenu('My Title');
        $fixture->setPourTousEnfants('My Title');
        $fixture->setActif('My Title');
        $fixture->setCreePar('My Title');
        $fixture->setDateCreation('My Title');
        $fixture->setComplete('My Title');
        $fixture->setArchive('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Exercice');

        // Use assertions to check that the properties are properly displayed.
        $this->markTestIncomplete('This test was generated');
    }

    public function testEdit(): void
    {
        $fixture = new Exercice();
        $fixture->setTitre('Value');
        $fixture->setType('Value');
        $fixture->setConsigne('Value');
        $fixture->setDifficulte('Value');
        $fixture->setDuree('Value');
        $fixture->setContenu('Value');
        $fixture->setPourTousEnfants('Value');
        $fixture->setActif('Value');
        $fixture->setCreePar('Value');
        $fixture->setDateCreation('Value');
        $fixture->setComplete('Value');
        $fixture->setArchive('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'exercice[titre]' => 'Something New',
            'exercice[type]' => 'Something New',
            'exercice[consigne]' => 'Something New',
            'exercice[difficulte]' => 'Something New',
            'exercice[duree]' => 'Something New',
            'exercice[contenu]' => 'Something New',
            'exercice[pour_tous_enfants]' => 'Something New',
            'exercice[actif]' => 'Something New',
            'exercice[cree_par]' => 'Something New',
            'exercice[date_creation]' => 'Something New',
            'exercice[complete]' => 'Something New',
            'exercice[archive]' => 'Something New',
        ]);

        self::assertResponseRedirects('/exercice');

        $fixture = $this->exerciceRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getTitre());
        self::assertSame('Something New', $fixture[0]->getType());
        self::assertSame('Something New', $fixture[0]->getConsigne());
        self::assertSame('Something New', $fixture[0]->getDifficulte());
        self::assertSame('Something New', $fixture[0]->getDuree());
        self::assertSame('Something New', $fixture[0]->getContenu());
        self::assertSame('Something New', $fixture[0]->getPourTousEnfants());
        self::assertSame('Something New', $fixture[0]->getActif());
        self::assertSame('Something New', $fixture[0]->getCreePar());
        self::assertSame('Something New', $fixture[0]->getDateCreation());
        self::assertSame('Something New', $fixture[0]->getComplete());
        self::assertSame('Something New', $fixture[0]->getArchive());

        $this->markTestIncomplete('This test was generated');
    }

    public function testRemove(): void
    {
        $fixture = new Exercice();
        $fixture->setTitre('Value');
        $fixture->setType('Value');
        $fixture->setConsigne('Value');
        $fixture->setDifficulte('Value');
        $fixture->setDuree('Value');
        $fixture->setContenu('Value');
        $fixture->setPourTousEnfants('Value');
        $fixture->setActif('Value');
        $fixture->setCreePar('Value');
        $fixture->setDateCreation('Value');
        $fixture->setComplete('Value');
        $fixture->setArchive('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/exercice');
        self::assertSame(0, $this->exerciceRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }
}
