<?php

namespace App\Tests\Controller;

use App\Entity\Contacts;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ContactsControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $contactRepository;
    private string $path = '/contacts/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->contactRepository = $this->manager->getRepository(Contacts::class);

        foreach ($this->contactRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Contact index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'contact[title]' => 'Testing',
            'contact[sujet]' => 'Testing',
            'contact[message_contact]' => 'Testing',
            'contact[email_contact]' => 'Testing',
            'contact[created_at_contact]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->contactRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Contacts();
        $fixture->setTitle('My Title');
        $fixture->setSujet('My Title');
        $fixture->setMessage_contact('My Title');
        $fixture->setEmail_contact('My Title');
        $fixture->setCreated_at_contact('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Contact');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Contacts();
        $fixture->setTitle('Value');
        $fixture->setSujet('Value');
        $fixture->setMessage_contact('Value');
        $fixture->setEmail_contact('Value');
        $fixture->setCreated_at_contact('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'contact[title]' => 'Something New',
            'contact[sujet]' => 'Something New',
            'contact[message_contact]' => 'Something New',
            'contact[email_contact]' => 'Something New',
            'contact[created_at_contact]' => 'Something New',
        ]);

        self::assertResponseRedirects('/contacts/');

        $fixture = $this->contactRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getTitle());
        self::assertSame('Something New', $fixture[0]->getSujet());
        self::assertSame('Something New', $fixture[0]->getMessage_contact());
        self::assertSame('Something New', $fixture[0]->getEmail_contact());
        self::assertSame('Something New', $fixture[0]->getCreated_at_contact());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Contacts();
        $fixture->setTitle('Value');
        $fixture->setSujet('Value');
        $fixture->setMessage_contact('Value');
        $fixture->setEmail_contact('Value');
        $fixture->setCreated_at_contact('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/contacts/');
        self::assertSame(0, $this->contactRepository->count([]));
    }
}
