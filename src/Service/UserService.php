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
     * @param string $email
     * @param string $username
     * @param string $password
     * 
     * @return array
     */
    public function register(string $email, string $username, string $password): array
    {
        $errors = $this->validateUserExists($username, $email);
        if (count($errors) > 0) {
            $response_array = [
                'data' => $errors,
                'code' => self::CONFLICT_ERR,
            ];
            return $response_array;
        }
        $user = new User($username);
        $user->setEmail($email);
        $user->setPassword($this->encoder->encodePassword($user, $password));
        $this->doctrine->persist($user);
        $this->doctrine->flush();

        return [
            'code' => self::SUCCESS,
            'data' => $user,
        ];
    }

    /**
     * Function validateUserExists() checks if user exists.
     *
     * In this function we first check if a user with the specified username
     * already exists or with the specified email and we return accordingly
     * the feedback to caller.
     *
     * @param string $username the username for a user.
     * @param string $email the email of a user.
     * 
     * @return array returns an array with errors for existing user.
     */
    private function validateUserExists(string $username, string $email)
    {
        $errors = [];
        $user_repo = $this->doctrine->getRepository(User::class);
        $already_user = $user_repo->findOneBy(["username" => $username]);
        if ($already_user) {
            $errors[] = [
                'property_path' => "username",
                "message" => "This username is already used",
            ];
        }
        $already_user = $user_repo->findOneBy(["email" => $email]);
        if ($already_user) {
            $errors[] = [
                'property_path' => "email",
                "message" => "This email is already used",
            ];
        }
        return $errors;
    }
}
