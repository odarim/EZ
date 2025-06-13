<?php

namespace App\Command;

use App\Entity\Account\Admin;
use App\Entity\Account\Customer;
use App\Repository\Account\UserRepository;
use App\Validator\CreateUserCommandValidator;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use function Symfony\Component\String\u;

#[AsCommand(
    name: 'app:create-user',
    description: 'Add a short description for your command',
)]
class CreateUserCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly CreateUserCommandValidator $validator,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $users
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp($this->getCommandHelp())
            // commands can optionally define arguments and/or options (mandatory and optional)
            // see https://symfony.com/doc/current/components/console/console_arguments.html
            ->addArgument('username', InputArgument::OPTIONAL, 'The username of the new user')
            ->addArgument('email', InputArgument::OPTIONAL, 'The email of the new user')
            ->addArgument('password', InputArgument::OPTIONAL, 'The plain password of the new user')
            ->addArgument('first-name', InputArgument::OPTIONAL, 'The first name of the new user')
            ->addArgument('last-name', InputArgument::OPTIONAL, 'The last name of the new user')
            ->addOption('admin', 'a', InputOption::VALUE_NONE, 'If set, the user is created as an administrator')
        ;
    }

    private function getCommandHelp(): string
    {
        return <<<'HELP'
            The <info>%command.name%</info> command creates new users and saves them in the database:
            
              <info>php %command.full_name%</info> <comment>username email password first-name last-name</comment>
              
            By default the command creates regular users. To create administrator users,
            add the <comment>--admin</comment> option:
            
              <info>php %command.full_name%</info>username email password <comment>--admin</comment>
              
            If you omit any of the three required arguments, the command will ask you to
            provide the missing values:
            
              # command will ask you for the password, first-name and last-name
              <info>php %command.full_name%</info> <comment>email</comment>
              
              # command will ask you for the first-name and last-name
              <info>php %command.full_name%</info> <comment>username password</comment>
              
              # command will ask you for all arguments
              <info>php %command.full_name%</info>
            HELP;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        if (null !== $input->getArgument('username') &&
            null !== $input->getArgument('email') &&
            null !== $input->getArgument('password') &&
            null !== $input->getArgument('last-name') &&
            null !== $input->getArgument('first-name')
        ) {
            return;
        }

        $this->io->title('Add Account Command Interactive Wizard');
        $this->io->text([
            'If you prefer to not use this interactive wizard, provide the',
            'arguments required by this command as follows:',
            '',
            ' $ php bin/console app:create-user johndoe email@example.com password john doe',
            '',
            'Now we\'ll ask you for the value of all the missing command arguments.',
        ]);

        // Ask for the email if it's not defined
        $username = $input->getArgument('username');
        if (null !== $username) {
            $this->io->text(' > <info>Username</info>: '.$username);
        } else {
            $username = $this->io->ask('Username', null, [$this->validator, 'validateUsername']);
            $input->setArgument('username', $username);
        }

        // Ask for the email if it's not defined
        $email = $input->getArgument('email');
        if (null !== $email) {
            $this->io->text(' > <info>Email</info>: '.$email);
        } else {
            $email = $this->io->ask('Email', null, [$this->validator, 'validateEmail']);
            $input->setArgument('email', $email);
        }

        // Ask for the password if it's not defined
        /** @var string|null $password */
        $password = $input->getArgument('password');

        if (null !== $password) {
            $this->io->text(' > <info>Password</info>: '.u('*')->repeat(u($password)->length()));
        } else {
            $password = $this->io->askHidden('Password (your type will be hidden)', [$this->validator, 'validatePassword']);
            $input->setArgument('password', $password);
        }

        // Ask for the first name if it's not defined
        $firstName = $input->getArgument('first-name');
        if (null !== $firstName) {
            $this->io->text(' > <info>First Name</info>: '.$firstName);
        } else {
            $firstName = $this->io->ask('First Name', null, [$this->validator, 'validateFirstName']);
            $input->setArgument('first-name', $firstName);
        }

        // Ask for the full name if it's not defined
        $lastName = $input->getArgument('last-name');
        if (null !== $lastName) {
            $this->io->text(' > <info>Last Name</info>: '.$lastName);
        } else {
            $lastName = $this->io->ask('Last Name', null, [$this->validator, 'validateLastName']);
            $input->setArgument('last-name', $lastName);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start('create-user-command');

        /** @var string $username */
        $username = $input->getArgument('username');

        /** @var string $email */
        $email = $input->getArgument('email');

        /** @var string $plainPassword */
        $plainPassword = $input->getArgument('password');

        /** @var string $firstName */
        $firstName = $input->getArgument('first-name');

        /** @var string $lastName */
        $lastName = $input->getArgument('last-name');

        /** @var string|null $isAdmin */
        $isAdmin = $input->getOption('admin');

        // make sure to validate the user data is correct
        $this->validateUserData($username, $email, $plainPassword, $firstName, $lastName);

        // create the user and hash its password
        $user = $isAdmin ? new Admin() : new Customer();

        $user->setUsername($username)
            ->setEmail($email)
            ->setFirstName($firstName)
            ->setLastName($lastName)
        ;

        // See https://symfony.com/doc/6.3/security.html#registering-the-user-hashing-passwords
        $hashedPassword = $this->userPasswordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->io->success(sprintf('%s was successfully created: %s (%s)', $isAdmin ? 'Administrator user' : 'Account', $user->getFullName(), $user->getEmail()));

        $event = $stopwatch->stop('create-user-command');
        if ($output->isVerbose()) {
            $this->io->comment(sprintf('New user database id: %d / Elapsed time: %.2f ms / Consumed memory: %.2f MB', $user->getId(), $event->getDuration(), $event->getMemory() / (1024 ** 2)));
        }

        return Command::SUCCESS;
    }

    private function validateUserData(string $username, string $email, string $plainPassword, string $firstName, string $lastName): void
    {
        $existingUserByUserName = $this->users->findOneBy(['username' => $username]);

        if (null !== $existingUserByUserName) {
            throw new RuntimeException(sprintf('There is already a user registered with the "%s" username.', $username));
        }

        $existingUserByEmail = $this->users->findOneBy(['email' => $email]);

        if (null !== $existingUserByEmail) {
            throw new RuntimeException(sprintf('There is already a user registered with the "%s" email.', $email));
        }

        // validate password and email if is not this input means interactive.
        $this->validator->validateUsername($username);
        $this->validator->validateEmail($email);
        $this->validator->validatePassword($plainPassword);
        $this->validator->validateFirstName($firstName);
        $this->validator->validateLastName($lastName);
    }
}
