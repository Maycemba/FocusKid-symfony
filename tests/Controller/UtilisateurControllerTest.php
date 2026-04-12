<?php

namespace App\Tests\Controller;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class UtilisateurControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    /** @var EntityRepository<Utilisateur> $utilisateurRepository */
    private EntityRepository $utilisateurRepository;
    private string $path = '/utilisateur/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->utilisateurRepository = $this->manager->getRepository(Utilisateur::class);

        foreach ($this->utilisateurRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Utilisateur index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'utilisateur[username]' => 'Testing',
            'utilisateur[email]' => 'Testing',
            'utilisateur[passwordHash]' => 'Testing',
            'utilisateur[role]' => 'Testing',
            'utilisateur[isActive]' => 'Testing',
            'utilisateur[createdAt]' => 'Testing',
            'utilisateur[notifications_email]' => 'Testing',
        ]);

        self::assertResponseRedirects('/utilisateur');

        self::assertSame(1, $this->utilisateurRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }

    public function testShow(): void
    {
        $fixture = new Utilisateur();
        $fixture->setUsername('My Title');
        $fixture->setEmail('My Title');
        $fixture->setPasswordHash('My Title');
        $fixture->setRole('My Title');
        $fixture->setIsActive('My Title');
        $fixture->setCreatedAt('My Title');
        $fixture->setNotificationsEmail('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Utilisateur');

        // Use assertions to check that the properties are properly displayed.
        $this->markTestIncomplete('This test was generated');
    }

    public function testEdit(): void
    {
        $fixture = new Utilisateur();
        $fixture->setUsername('Value');
        $fixture->setEmail('Value');
        $fixture->setPasswordHash('Value');
        $fixture->setRole('Value');
        $fixture->setIsActive('Value');
        $fixture->setCreatedAt('Value');
        $fixture->setNotificationsEmail('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'utilisateur[username]' => 'Something New',
            'utilisateur[email]' => 'Something New',
            'utilisateur[passwordHash]' => 'Something New',
            'utilisateur[role]' => 'Something New',
            'utilisateur[isActive]' => 'Something New',
            'utilisateur[createdAt]' => 'Something New',
            'utilisateur[notifications_email]' => 'Something New',
        ]);

        self::assertResponseRedirects('/utilisateur');

        $fixture = $this->utilisateurRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getUsername());
        self::assertSame('Something New', $fixture[0]->getEmail());
        self::assertSame('Something New', $fixture[0]->getPasswordHash());
        self::assertSame('Something New', $fixture[0]->getRole());
        self::assertSame('Something New', $fixture[0]->getIsActive());
        self::assertSame('Something New', $fixture[0]->getCreatedAt());
        self::assertSame('Something New', $fixture[0]->getNotificationsEmail());

        $this->markTestIncomplete('This test was generated');
    }

    public function testRemove(): void
    {
        $fixture = new Utilisateur();
        $fixture->setUsername('Value');
        $fixture->setEmail('Value');
        $fixture->setPasswordHash('Value');
        $fixture->setRole('Value');
        $fixture->setIsActive('Value');
        $fixture->setCreatedAt('Value');
        $fixture->setNotificationsEmail('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/utilisateur');
        self::assertSame(0, $this->utilisateurRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }
}
