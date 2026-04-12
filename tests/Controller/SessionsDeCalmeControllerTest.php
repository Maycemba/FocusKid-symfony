<?php

namespace App\Tests\Controller;

use App\Entity\SessionsDeCalme;
use App\Repository\SessionsDeCalmeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SessionsDeCalmeControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    /** @var EntityRepository<SessionsDeCalme> $sessionsDeCalmeRepository */
    private EntityRepository $sessionsDeCalmeRepository;
    private string $path = '/sessions/de/calme/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->sessionsDeCalmeRepository = $this->manager->getRepository(SessionsDeCalme::class);

        foreach ($this->sessionsDeCalmeRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('SessionsDeCalme index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'sessions_de_calme[type_activite]' => 'Testing',
            'sessions_de_calme[declencheur]' => 'Testing',
            'sessions_de_calme[duree_prevue]' => 'Testing',
            'sessions_de_calme[duree_reelle]' => 'Testing',
            'sessions_de_calme[horodatage]' => 'Testing',
            'sessions_de_calme[feedback_enfant]' => 'Testing',
            'sessions_de_calme[note_parent]' => 'Testing',
            'sessions_de_calme[utilisateur]' => 'Testing',
        ]);

        self::assertResponseRedirects('/sessions/de/calme');

        self::assertSame(1, $this->sessionsDeCalmeRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }

    public function testShow(): void
    {
        $fixture = new SessionsDeCalme();
        $fixture->setTypeActivite('My Title');
        $fixture->setDeclencheur('My Title');
        $fixture->setDureePrevue('My Title');
        $fixture->setDureeReelle('My Title');
        $fixture->setHorodatage('My Title');
        $fixture->setFeedbackEnfant('My Title');
        $fixture->setNoteParent('My Title');
        $fixture->setUtilisateur('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('SessionsDeCalme');

        // Use assertions to check that the properties are properly displayed.
        $this->markTestIncomplete('This test was generated');
    }

    public function testEdit(): void
    {
        $fixture = new SessionsDeCalme();
        $fixture->setTypeActivite('Value');
        $fixture->setDeclencheur('Value');
        $fixture->setDureePrevue('Value');
        $fixture->setDureeReelle('Value');
        $fixture->setHorodatage('Value');
        $fixture->setFeedbackEnfant('Value');
        $fixture->setNoteParent('Value');
        $fixture->setUtilisateur('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'sessions_de_calme[type_activite]' => 'Something New',
            'sessions_de_calme[declencheur]' => 'Something New',
            'sessions_de_calme[duree_prevue]' => 'Something New',
            'sessions_de_calme[duree_reelle]' => 'Something New',
            'sessions_de_calme[horodatage]' => 'Something New',
            'sessions_de_calme[feedback_enfant]' => 'Something New',
            'sessions_de_calme[note_parent]' => 'Something New',
            'sessions_de_calme[utilisateur]' => 'Something New',
        ]);

        self::assertResponseRedirects('/sessions/de/calme');

        $fixture = $this->sessionsDeCalmeRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getTypeActivite());
        self::assertSame('Something New', $fixture[0]->getDeclencheur());
        self::assertSame('Something New', $fixture[0]->getDureePrevue());
        self::assertSame('Something New', $fixture[0]->getDureeReelle());
        self::assertSame('Something New', $fixture[0]->getHorodatage());
        self::assertSame('Something New', $fixture[0]->getFeedbackEnfant());
        self::assertSame('Something New', $fixture[0]->getNoteParent());
        self::assertSame('Something New', $fixture[0]->getUtilisateur());

        $this->markTestIncomplete('This test was generated');
    }

    public function testRemove(): void
    {
        $fixture = new SessionsDeCalme();
        $fixture->setTypeActivite('Value');
        $fixture->setDeclencheur('Value');
        $fixture->setDureePrevue('Value');
        $fixture->setDureeReelle('Value');
        $fixture->setHorodatage('Value');
        $fixture->setFeedbackEnfant('Value');
        $fixture->setNoteParent('Value');
        $fixture->setUtilisateur('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/sessions/de/calme');
        self::assertSame(0, $this->sessionsDeCalmeRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }
}
