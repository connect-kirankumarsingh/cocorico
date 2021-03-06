<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\UserBundle\Entity;

use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\CoreBundle\Entity\BookingBankWire;
use Cocorico\CoreBundle\Entity\BookingPayinRefund;
use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\MessageBundle\Entity\Message;
use Cocorico\ReviewBundle\Entity\Review;
use Cocorico\UserBundle\Model\ListingAlertInterface;
use Cocorico\UserBundle\Validator\Constraints as CocoricoUserAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FOS\MessageBundle\Model\ParticipantInterface;
use FOS\UserBundle\Entity\User as BaseUser;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User
 *
 * @CocoricoUserAssert\User()
 *
 * @ORM\Entity(repositoryClass="Cocorico\UserBundle\Repository\UserRepository")
 *
 * @UniqueEntity(
 *      fields={"email"},
 *      groups={"CocoricoRegistration", "CocoricoProfile", "CocoricoProfileContact", "default"},
 *      message="cocorico_user.email.already_used"
 * )
 *
 * @UniqueEntity(
 *      fields={"username"},
 *      groups={"CocoricoRegistration", "CocoricoProfile", "CocoricoProfileContact", "default"},
 *      message="cocorico_user.email.already_used"
 * )
 *
 * @ORM\Table(name="`user`",indexes={
 *    @ORM\Index(name="slug_u_idx", columns={"slug"}),
 *    @ORM\Index(name="enabled_idx", columns={"enabled"}),
 *    @ORM\Index(name="email_idx", columns={"email"})
 *  })
 *
 */
class User extends BaseUser implements ParticipantInterface
{
    use ORMBehaviors\Timestampable\Timestampable;
    use ORMBehaviors\Translatable\Translatable;
    use ORMBehaviors\Sluggable\Sluggable;

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Cocorico\CoreBundle\Model\CustomIdGenerator")
     *
     * @var integer
     */
    protected $id;

    /**
     * @var string
     *
     * @Assert\Email(message="cocorico_user.email.invalid", strict=true, groups={"CocoricoRegistration", "CocoricoProfile", "CocoricoProfileContact"})
     *
     * @Assert\NotBlank(message="cocorico_user.email.blank", groups={"CocoricoRegistration", "CocoricoProfile", "CocoricoProfileContact"})
     *
     * @Assert\Length(
     *     min=3,
     *     max="255",
     *     minMessage="cocorico_user.username.short",
     *     maxMessage="cocorico_user.username.long",
     *     groups={"CocoricoRegistration", "CocoricoProfile", "CocoricoProfileContact"}
     * )
     */
    protected $email;

    /**
     * @ORM\Column(name="last_name", type="string", length=100)
     *
     * @Assert\NotBlank(message="cocorico_user.last_name.blank", groups={
     *  "CocoricoRegistration", "CocoricoProfile", "CocoricoProfilePayment"
     * })
     *
     * @Assert\Length(
     *     min=3,
     *     max="100",
     *     minMessage="cocorico_user.last_name.short",
     *     maxMessage="cocorico_user.last_name.long",
     *     groups={"CocoricoRegistration", "CocoricoProfile", "CocoricoProfilePayment"}
     * )
     */
    protected $lastName;

    /**
     * @ORM\Column(name="first_name", type="string", length=100)
     *
     * @Assert\NotBlank(message="cocorico_user.first_name.blank", groups={
     *  "CocoricoRegistration", "CocoricoProfile", "CocoricoProfilePayment"
     * })
     *
     * @Assert\Length(
     *     min=3,
     *     max="100",
     *     minMessage="cocorico_user.first_name.short",
     *     maxMessage="cocorico_user.first_name.long",
     *     groups={"CocoricoRegistration", "CocoricoProfile", "CocoricoProfilePayment"}
     * )
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="phone_prefix", type="string", length=6, nullable=true)
     */
    protected $phonePrefix = '+33';

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=16, nullable=true)
     */
    protected $phone;


    /**
     * @var \DateTime $birthday
     *
     * @ORM\Column(name="birthday", type="date")
     *
     * @Assert\NotBlank(message="cocorico_user.birthday.blank", groups={
     *  "CocoricoRegistration", "CocoricoProfilePayment"
     * })
     *
     */
    protected $birthday;


    /**
     * @var string
     *
     * @ORM\Column(name="nationality", type="string", length=3, nullable=true)
     */
    protected $nationality = "FR";

    /**
     * @var string
     *
     * @ORM\Column(name="country_of_residence", type="string", length=3, nullable=true)
     *
     * @Assert\NotBlank(message="cocorico_user.country_of_residence.blank", groups={
     *  "CocoricoRegistration", "CocoricoProfilePayment"
     * })
     */
    protected $countryOfResidence = "FR";

    /**
     * @var string
     *
     * @ORM\Column(name="profession", type="string", length=50, nullable=true)
     */
    protected $profession;

    /**
     * @var string
     *
     * @ORM\Column(name="iban", type="string", length=45, nullable=true)
     *
     * @Assert\Iban(message = "cocorico_user.iban.invalid", groups={
     *  "CocoricoProfilePayment"
     * }))
     *
     * @Assert\NotBlank(message="cocorico_user.iban.blank", groups={
     *  "CocoricoProfilePayment"
     * })
     *
     */
    protected $iban;

    /**
     * @var string
     *
     * @ORM\Column(name="bic", type="string", length=25, nullable=true)
     *
     * @Assert\NotBlank(message="cocorico_user.bic.blank", groups={
     *  "CocoricoProfilePayment"
     * })
     */
    protected $bic;

    /**
     * @var string
     *
     * @ORM\Column(name="bank_owner_name", type="string", length=100, nullable=true)
     *
     * @Assert\NotBlank(message="cocorico_user.bank_owner_name.blank", groups={
     *  "CocoricoProfilePayment"
     * })
     */
    protected $bankOwnerName;

    /**
     * @var string
     *
     * @ORM\Column(name="bank_owner_address", type="string", length=255, nullable=true)
     *
     * @Assert\NotBlank(message="cocorico_user.bank_owner_address.blank", groups={
     *  "CocoricoProfilePayment"
     * })
     */
    protected $bankOwnerAddress;

    /**
     * @ORM\Column(name="annual_income", type="decimal", precision=10, scale=2, nullable=true)
     *
     * @var integer
     */
    protected $annualIncome;

    /**
     * @Assert\Length(
     *      min = 6,
     *      minMessage = "{{ limit }}cocorico_user.password.short",
     * )
     *
     * @var string
     */
    protected $plainPassword;

    /**
     *
     * @ORM\Column(name="phone_verified", type="boolean", nullable=true)
     *
     * @var boolean
     */
    protected $phoneVerified;

    /**
     *
     * @ORM\Column(name="email_verified", type="boolean", nullable=true)
     *
     * @var boolean
     */
    protected $emailVerified;

    /**
     *
     * @ORM\Column(name="id_card_verified", type="boolean", nullable=true)
     *
     * @var boolean
     */
    protected $idCardVerified;

    /**
     *
     * @ORM\Column(name="nb_bookings_offerer", type="smallint", nullable=true)
     *
     * @var int
     */
    protected $nbBookingsOfferer;

    /**
     *
     * @ORM\Column(name="nb_bookings_asker", type="smallint", nullable=true)
     *
     * @var int
     */
    protected $nbBookingsAsker;

    /**
     *
     * @ORM\Column(name="fee_as_asker", type="smallint", nullable=true)
     *
     * @var integer Percent
     */
    protected $feeAsAsker;

    /**
     * @ORM\Column(name="fee_as_offerer", type="smallint", nullable=true)
     *
     * @var integer Percent
     */
    protected $feeAsOfferer;

    /**
     * @ORM\Column(name="average_rating_as_asker", type="smallint", nullable=true)
     *
     * @var integer
     */
    protected $averageAskerRating;


    /**
     * @ORM\Column(name="average_rating_as_offerer", type="smallint", nullable=true)
     *
     * @var integer
     */
    protected $averageOffererRating;

    /**
     * @ORM\Column(name="mother_tongue", type="string", length=5, nullable=true)
     *
     * @Assert\NotBlank(message="cocorico_user.motherTongue.blank", groups={"CocoricoProfile"})
     *
     * @var string
     */
    protected $motherTongue;

    /**
     * @ORM\Column(name="answer_delay", type="integer", nullable=true)
     *
     * @var integer
     */
    protected $answerDelay;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\MessageBundle\Entity\Message", mappedBy="sender", cascade={"remove"}, orphanRemoval=true)
     */
    private $messages;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\ReviewBundle\Entity\Review", mappedBy="reviewBy", cascade={"remove"}, orphanRemoval=true)
     */
    private $reviewsBy;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\ReviewBundle\Entity\Review", mappedBy="reviewTo", cascade={"remove"}, orphanRemoval=true)
     */
    private $reviewsTo;

    /**
     * @ORM\OneToOne(targetEntity="Cocorico\UserBundle\Entity\UserFacebook", mappedBy="user", cascade={"remove"}, orphanRemoval=true)
     */
    private $userFacebook;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\CoreBundle\Entity\Listing", mappedBy="user", cascade={"persist", "remove"})
     * @ORM\OrderBy({"createdAt" = "desc"})
     *
     * @var Listing[]
     */
    private $listings;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\UserBundle\Entity\UserAddress", mappedBy="user", cascade={"persist", "remove"})
     *
     * @var UserAddress[]
     */
    private $addresses;

    /**
     * For Asserts : @see \Cocorico\UserBundle\Validator\Constraints\UserValidator
     *
     * @ORM\OneToMany(targetEntity="UserImage", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "asc"})
     *
     * @var UserImage[]
     */
    protected $images;

    /**
     * @ORM\OneToMany(targetEntity="UserLanguage", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     * @var UserLanguage[]
     */
    protected $languages;

    /**
     *
     * @ORM\OneToMany(targetEntity="Cocorico\CoreBundle\Entity\Booking", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"createdAt" = "desc"})
     *
     * @var Booking[]
     */
    protected $bookings;


    /**
     * @ORM\OneToMany(targetEntity="Cocorico\CoreBundle\Entity\BookingBankWire", mappedBy="user", cascade={"persist", "remove"})
     * @ORM\OrderBy({"createdAt" = "desc"})
     *
     * @var BookingBankWire[]
     */
    private $bookingBankWires;

    /**
     * @ORM\OneToMany(targetEntity="Cocorico\CoreBundle\Entity\BookingPayinRefund", mappedBy="user", cascade={"persist", "remove"})
     * @ORM\OrderBy({"createdAt" = "desc"})
     *
     * @var BookingPayinRefund[]
     */
    private $bookingPayinRefunds;

    /**
     *
     * @ORM\OneToMany(targetEntity="Cocorico\UserBundle\Model\ListingAlertInterface", mappedBy="user", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $listingAlerts;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->listings = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->languages = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->reviewsBy = new ArrayCollection();
        $this->reviewsTo = new ArrayCollection();
        $this->addresses = new ArrayCollection();
        $this->bookingBankWires = new ArrayCollection();
        $this->bookingPayinRefunds = new ArrayCollection();
        $this->listingAlerts = new ArrayCollection();
        parent::__construct();
    }

    public function getSluggableFields()
    {
        return ['firstName', 'id'];
    }

    /**
     * Translation proxy
     *
     * @param $method
     * @param $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return $this->proxyCurrentLocaleTranslation($method, $arguments);
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     *
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    public function getName()
    {
        return $this->firstName . " " . ucfirst(substr($this->lastName, 0, 1) . ".");
    }

    public function setEmail($email)
    {
        $email = is_null($email) ? '' : $email;
        parent::setEmail($email);
        $this->setUsername($email);
    }


    /**
     * Set phoneVerified
     *
     * @param boolean $phoneVerified
     * @return User
     */
    public function setPhoneVerified($phoneVerified)
    {
        $this->phoneVerified = $phoneVerified;

        return $this;
    }

    /**
     * Get phoneVerified
     *
     * @return boolean
     */
    public function getPhoneVerified()
    {
        return $this->phoneVerified;
    }

    /**
     * Set emailVerified
     *
     * @param boolean $emailVerified
     * @return User
     */
    public function setEmailVerified($emailVerified)
    {
        $this->emailVerified = $emailVerified;

        return $this;
    }

    /**
     * Get emailVerified
     *
     * @return boolean
     */
    public function getEmailVerified()
    {
        return $this->emailVerified;
    }

    /**
     * Set idCardVerified
     *
     * @param boolean $idCardVerified
     * @return User
     */
    public function setIdCardVerified($idCardVerified)
    {
        $this->idCardVerified = $idCardVerified;

        return $this;
    }

    /**
     * Get idCardVerified
     *
     * @return boolean
     */
    public function getIdCardVerified()
    {
        return $this->idCardVerified;
    }

    /**
     * Set nbBookingsOfferer
     *
     * @param int $nbBookingsOfferer
     * @return User
     */
    public function setNbBookingsOfferer($nbBookingsOfferer)
    {
        $this->nbBookingsOfferer = $nbBookingsOfferer;

        return $this;
    }

    /**
     * Get nbBookingsOfferer
     *
     * @return int
     */
    public function getNbBookingsOfferer()
    {
        return $this->nbBookingsOfferer;
    }

    /**
     * @return int
     */
    public function getNbBookingsAsker()
    {
        return $this->nbBookingsAsker;
    }

    /**
     * @param int $nbBookingsAsker
     */
    public function setNbBookingsAsker($nbBookingsAsker)
    {
        $this->nbBookingsAsker = $nbBookingsAsker;
    }

    /**
     * @return int
     */
    public function getFeeAsAsker()
    {
        return $this->feeAsAsker;
    }

    /**
     * @param int $feeAsAsker
     */
    public function setFeeAsAsker($feeAsAsker)
    {
        $this->feeAsAsker = $feeAsAsker;
    }

    /**
     * @return int
     */
    public function getFeeAsOfferer()
    {
        return $this->feeAsOfferer;
    }

    /**
     * @param int $feeAsOfferer
     */
    public function setFeeAsOfferer($feeAsOfferer)
    {
        $this->feeAsOfferer = $feeAsOfferer;
    }

    /**
     * @return \DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @param \DateTime $birthday
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;
    }

    /**
     * @return string
     */
    public function getNationality()
    {
        return $this->nationality;
    }

    /**
     * @param string $nationality
     */
    public function setNationality($nationality)
    {
        $this->nationality = $nationality;
    }

    /**
     * @return string
     */
    public function getProfession()
    {
        return $this->profession;
    }

    /**
     * @param string $profession
     */
    public function setProfession($profession)
    {
        $this->profession = $profession;
    }

    /**
     * @return string
     */
    public function getIban()
    {
        return $this->iban;
    }

    /**
     * @param string $iban
     */
    public function setIban($iban)
    {
        $this->iban = $iban;
    }

    /**
     * @return string
     */
    public function getBic()
    {
        return $this->bic;
    }

    /**
     * @param string $bic
     */
    public function setBic($bic)
    {
        $this->bic = $bic;
    }

    /**
     * @return string
     */
    public function getBankOwnerName()
    {
        return $this->bankOwnerName;
    }

    /**
     * @param string $bankOwnerName
     */
    public function setBankOwnerName($bankOwnerName)
    {
        $this->bankOwnerName = $bankOwnerName;
    }

    /**
     * @return string
     */
    public function getBankOwnerAddress()
    {
        return $this->bankOwnerAddress;
    }

    /**
     * @param string $bankOwnerAddress
     */
    public function setBankOwnerAddress($bankOwnerAddress)
    {
        $this->bankOwnerAddress = $bankOwnerAddress;
    }

    /**
     * @return int
     */
    public function getAnnualIncome()
    {
        return $this->annualIncome;
    }

    /**
     * @param int $annualIncome
     */
    public function setAnnualIncome($annualIncome)
    {
        $this->annualIncome = $annualIncome;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getPhonePrefix()
    {
        return $this->phonePrefix;
    }

    /**
     * @param string $phonePrefix
     */
    public function setPhonePrefix($phonePrefix)
    {
        $this->phonePrefix = $phonePrefix;
    }


    /**
     * @return string
     */
    public function getCountryOfResidence()
    {
        return $this->countryOfResidence;
    }

    /**
     * @param string $countryOfResidence
     */
    public function setCountryOfResidence($countryOfResidence)
    {
        $this->countryOfResidence = $countryOfResidence;
    }


    /**
     * Set averageAskerRating
     *
     * @param  integer $averageAskerRating
     * @return Listing
     */
    public function setAverageAskerRating($averageAskerRating)
    {
        $this->averageAskerRating = $averageAskerRating;

        return $this;
    }

    /**
     * Get averageAskerRating
     *
     * @return integer
     */
    public function getAverageAskerRating()
    {
        return $this->averageAskerRating;
    }


    /**
     * Set averageOffererRating
     *
     * @param  integer $averageOffererRating
     * @return Listing
     */
    public function setAverageOffererRating($averageOffererRating)
    {
        $this->averageOffererRating = $averageOffererRating;

        return $this;
    }

    /**
     * Get averageOffererRating
     *
     * @return integer
     */
    public function getAverageOffererRating()
    {
        return $this->averageOffererRating;
    }

    /**
     * Set answerDelay
     *
     * @param  integer $answerDelay
     * @return Listing
     */
    public function setAnswerDelay($answerDelay)
    {
        $this->answerDelay = $answerDelay;

        return $this;
    }

    /**
     * Get answerDelay
     *
     * @return integer
     */
    public function getAnswerDelay()
    {
        return $this->answerDelay;
    }

    /**
     * @return string
     */
    public function getMotherTongue()
    {
        return $this->motherTongue;
    }

    /**
     * @param string $motherTongue
     */
    public function setMotherTongue($motherTongue)
    {
        $this->motherTongue = $motherTongue;
    }

    /**
     * @return mixed
     */
    public function getUserFacebook()
    {
        return $this->userFacebook;
    }

    /**
     * @param userFacebook $userFacebook
     */
    public function setUserFacebook($userFacebook)
    {
        $userFacebook->setUser($this);
        $this->userFacebook = $userFacebook;

    }


    public function getFullName()
    {
        return implode(' ', array_filter(array($this->getFirstName(), $this->getLastName())));
    }

    public function __toString()
    {
        return $this->getFullName();
    }


    /**
     * Add listings
     *
     * @param  Listing $listing
     * @return User
     */
    public function addListing(Listing $listing)
    {
        $this->listings[] = $listing;

        return $this;
    }

    /**
     * Remove listings
     *
     * @param Listing $listing
     */
    public function removeListing(Listing $listing)
    {
        $this->listings->removeElement($listing);
    }

    /**
     * Get listings
     *
     * @return Listing[]|\Doctrine\Common\Collections\Collection
     */
    public function getListings()
    {
        return $this->listings;
    }

    /**
     * Add images
     *
     * @param \Cocorico\UserBundle\Entity\UserImage $image
     *
     * @return Listing
     */
    public function addImage(UserImage $image)
    {
        $image->setUser($this); //Because the owning side of this relation is user image
        $this->images[] = $image;

        return $this;
    }

    /**
     * Remove images
     *
     * @param \Cocorico\UserBundle\Entity\UserImage $image
     */
    public function removeImage(UserImage $image)
    {
        $this->images->removeElement($image);
    }

    /**
     * Get images
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * Add language
     *
     * @param \Cocorico\UserBundle\Entity\UserLanguage $language
     *
     * @return Listing
     */
    public function addLanguage(UserLanguage $language)
    {
        $language->setUser($this);
        $this->languages[] = $language;

        return $this;
    }

    /**
     * Remove language
     *
     * @param \Cocorico\UserBundle\Entity\UserLanguage $language
     */
    public function removeLanguage(UserLanguage $language)
    {
        $this->languages->removeElement($language);
    }

    /**
     * Get languages
     *
     * @return \Doctrine\Common\Collections\Collection|UserLanguage[]
     */
    public function getLanguages()
    {
        return $this->languages;
    }


    /**
     * @return mixed
     */
    public function getBookings()
    {
        return $this->bookings;
    }

    /**
     * @param ArrayCollection|Booking[] $bookings
     */
    public function setBookings(ArrayCollection $bookings)
    {
        foreach ($bookings as $booking) {
            $booking->setUser($this);
        }

        $this->bookings = $bookings;
    }

    /**
     * @return mixed
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param ArrayCollection|Message[] $messages
     */
    public function setMessages(ArrayCollection $messages)
    {
        foreach ($messages as $message) {
            $message->setSender($this);
        }

        $this->messages = $messages;
    }


    /**
     * @return mixed
     */
    public function getReviewsBy()
    {
        return $this->reviewsBy;
    }

    /**
     * @param ArrayCollection|Message[] $reviewsBy
     */
    public function setReviewsBy(ArrayCollection $reviewsBy)
    {
        foreach ($reviewsBy as $review) {
            $review->setReviewBy($this);
        }

        $this->reviewsBy = $reviewsBy;
    }

    /**
     * @return mixed
     */
    public function getReviewsTo()
    {
        return $this->reviewsTo;
    }

    /**
     * @param ArrayCollection|Review[] $reviewsTo
     */
    public function setReviewsTo(ArrayCollection $reviewsTo)
    {
        foreach ($reviewsTo as $review) {
            $review->setReviewTo($this);
        }

        $this->reviewsTo = $reviewsTo;
    }


    /**
     * Add Address
     *
     * @param  UserAddress $address
     * @return User
     */
    public function addAddress(UserAddress $address)
    {
        $address->setUser($this);
        $this->addresses[] = $address;

        return $this;
    }

    /**
     * Remove Address
     *
     * @param UserAddress $address
     */
    public function removeAddress(UserAddress $address)
    {
        $this->addresses->removeElement($address);
    }

    /**
     * Get addresses
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAddresses()
    {
        return $this->addresses;
    }


    /**
     * @return BookingBankWire[]
     */
    public function getBookingBankWires()
    {
        return $this->bookingBankWires;
    }

    /**
     * @param ArrayCollection|BookingBankWire[] $bookingBankWires
     */
    public function setBookingBankWires(ArrayCollection $bookingBankWires)
    {
        foreach ($bookingBankWires as $bookingBankWire) {
            $bookingBankWire->setUser($this);
        }

        $this->bookingBankWires = $bookingBankWires;
    }


    /**
     * @return BookingPayinRefund[]
     */
    public function getBookingPayinRefunds()
    {
        return $this->bookingPayinRefunds;
    }

    /**
     * @param ArrayCollection|BookingPayinRefund[] $bookingPayinRefunds
     */
    public function setBookingPayinRefunds(ArrayCollection $bookingPayinRefunds)
    {
        foreach ($bookingPayinRefunds as $bookingPayinRefund) {
            $bookingPayinRefund->setUser($this);
        }

        $this->bookingPayinRefunds = $bookingPayinRefunds;
    }

    /**
     * Add ListingAlert
     *
     * @param  ListingAlertInterface $listingAlert
     * @return User
     */
    public function addListingAlert($listingAlert)
    {
        $listingAlert->setUser($this);
        $this->listingAlerts[] = $listingAlert;

        return $this;
    }

    /**
     * Remove ListingAlert
     *
     * @param ListingAlertInterface $listingAlert
     */
    public function removeListingAlert($listingAlert)
    {
        $this->listingAlerts->removeElement($listingAlert);
    }

    /**
     * Get ListingAlerts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getListingAlerts()
    {
        return $this->listingAlerts;
    }

    /**
     * @param ArrayCollection $listingAlerts
     * @return $this
     */
    public function setListingAlerts(ArrayCollection $listingAlerts)
    {
        foreach ($listingAlerts as $listingAlert) {
            $listingAlert->setUser($this);
        }

        $this->listingAlerts = $listingAlerts;
    }


    /**
     * @param int  $minImages
     * @param bool $strict
     *
     * @return array
     */
    public function getCompletionInformations($minImages, $strict = true)
    {
        return array(
            "description" => (
                ($strict && $this->getDescription()) ||
                (!$strict && strlen($this->getDescription()) > 250)
            ) ? 1 : 0,
            "image" => (
                ($strict && count($this->getImages()) >= $minImages) ||
                (!$strict && count($this->getImages()) > $minImages)
            ) ? 1 : 0,
        );
    }


    /**
     * Guess preferred site language from motherTongue, and spoken languages and  sites locales enabled
     *
     * todo: Add "preferred language" field to user entity and set it by default to mother tongue while registration, add it to editable fields and add it to the checked fields of this method.
     *
     * @param array  $siteLocales
     * @param string $defaultLocale
     * @return string
     */
    public function guessPreferredLanguage($siteLocales, $defaultLocale)
    {
        if ($this->getMotherTongue() && in_array($this->getMotherTongue(), $siteLocales)) {
            return $this->getMotherTongue();
        } elseif ($this->getLanguages()->count()) {
            foreach ($this->getLanguages() as $language) {
                if (in_array($language->getCode(), $siteLocales)) {
                    return $language->getCode();
                }
            }
        }

        return $defaultLocale;
    }
}
