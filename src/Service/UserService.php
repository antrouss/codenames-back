<?php

/**
 * This class is responsible for user actions.
 *
 * PHP version 7.4
 *
 * @category   Service
 * @author     Antony Roussos <antrouss4@gmail.com>
 * @version    0.0.1
 */

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserService extends BaseService
{
    /**
     * @var EntityManagerInterface
     */
    private $doctrine;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * @param EntityManagerInterface $doctrine
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(EntityManagerInterface $doctrine, UserPasswordEncoderInterface $encoder)
    {
        $this->doctrine = $doctrine;
        $this->encoder = $encoder;
    }

    /**
     * Method used to register a new user.
     *
     * @param string $username
     * @param string $password
     * 
     * @return array
     */
    public function register(string $username, string $password): array
    {
        /**
         * @var UserRepository
         */
        $user_repo = $this->doctrine->getRepository(User::class);
        $user = $user_repo->findOneBy(['username' => $username]);
        if (!is_null($user)) {
            return [
                'code' => self::CONFLICT_ERR,
                'data' => [
                    'message' => "This username is already used. Please use a different username.",
                ],
            ];
        }
        $user = new User($username);
        $user->setPassword($this->encoder->encodePassword($user, $password));
        $this->doctrine->persist($user);
        $this->doctrine->flush();

        return [
            'code' => self::SUCCESS,
            'data' => $user,
        ];
    }
}
