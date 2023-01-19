<?php
/**
 * Created by TutMee Co.
 * User: Domenik88(kataevevgenii@gmail.com)
 * Date: 24.12.2020
 *
 * @package viksemenov20
 */

namespace App\Service\User;

use App\Entity\User;
use App\Model\UserProfileModel;
use App\Repository\ListingRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserService
{
    private UserRepository $userRepository;
    private ListingRepository $listingRepository;
    private EntityManagerInterface $entityManager;
    private UserPasswordEncoderInterface $encoder;

    public function __construct(
        UserRepository               $userRepository,
        ListingRepository            $listingRepository,
        EntityManagerInterface       $entityManager,
        UserPasswordEncoderInterface $encoder
    )
    {
        $this->userRepository = $userRepository;
        $this->listingRepository = $listingRepository;
        $this->entityManager = $entityManager;
        $this->encoder = $encoder;
    }

    public function createUser(
        string  $email,
        string  $password,
        string  $phoneNumber,
        ?string $name = null
    ): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setName($name);
        $user->setPhoneNumber($phoneNumber);
        $user->setRoles([UserConstants::RoleUser]);
        $user->setPassword($this->encoder->encodePassword($user, $password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function updateUserProfile(User $user, UserProfileModel $profileModel): User
    {
        $user->setEmail($profileModel->getEmail());
        $user->setName($profileModel->getName());
        $user->setAddress($profileModel->getAddress());
        $user->setState($profileModel->getState());
        $user->setCity($profileModel->getCity());
        $user->setPostal($profileModel->getPostalCode());
        $user->setPhoneNumber($profileModel->getPhoneNumber());
        $user->setAbout($profileModel->getAbout());
        $user->setType($profileModel->getUserType());

        $this->entityManager->flush();

        return $user;
    }

    public function toggleFavoriteListing(string $listingId, int $userId)
    {
        // TODO: add listing in favorites to user and add user identificator here and logic in twig side
        $user = $this->userRepository->find($userId);
        $listing = $this->listingRepository->findOneBy(
            [
                'id' => $listingId,
            ]
        );
        if (!$user->getFavoriteListings()->contains($listing)) {
            $user->addFavoriteListing($listing);
        } else {
            $user->removeFavoriteListing($listing);
        }

        $this->entityManager->flush();

        return true;
    }

    public function getUserList(
        $search,
        int $currentPage = 1,
        int $limit = 50,
        int $offset = 0
    ): AdminUsersResult
    {
        $countWhere = 0;
        $query = $this->userRepository->createQueryBuilder('u');

        if ($search['name']) {
            $this->addWhere($query, 'u.name', 'name', $countWhere);
            $countWhere++;
        }

        if ($search['email']) {
            $query = $this->addWhere($query, 'u.email', 'email', $countWhere);
            $countWhere++;
        }

        if ($search['phone']) {
            $query = $this->addWhere($query, 'u.phoneNumber', 'phone', $countWhere);
        }

        foreach ($search as $key => $value) {
            if ($value) $query->setParameter($key, '%' . $value . '%');
        }

        $totalUsers = $this->countUsers($query);

        $query->setFirstResult($offset);
        $query->setMaxResults($limit);
        $query->orderBy('u.id', 'desc');

        $results = $query->getQuery()->getResult();

        $pageCounter = 1;
        if (count($results) > 0) {
            $pageCounter = ceil($totalUsers / $limit);
        }

        return new AdminUsersResult($totalUsers, $results, $currentPage, $pageCounter);
    }

    public function countUsers($query)
    {
        return count($query->getQuery()->getResult());
    }

    private function addWhere($query, $field, $value, $countWhere)
    {
        if ($countWhere >= 1) {
            return $query->andWhere($field . ' LIKE :' . $value);
        }

        return $query->where($field . ' LIKE :' . $value);
    }
}