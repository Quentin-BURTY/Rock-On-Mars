<?php

namespace App\Controller;

use App\Model\ActivityManager;
use App\Model\GatheringManager;
use App\Model\MemberManager;
use App\Service\ApiGet;
use Symfony\Component\HttpClient\HttpClient;
use App\Service\ValidationService;

class ActivityController extends AbstractController
{
    /**
     * @var ValidationService Service de validation
     */
    private ValidationService $validationService;


    public function __construct()
    {
        parent::__construct();

        $this->validationService = new ValidationService();
    }

    /**
     * List items
     */
    public function index(): string
    {
        session_start();

        if (!isset($_SESSION["user"])) {
            return $this->twig->render("Home/index.html.twig");
        } else {
            $activityManager = new ActivityManager();
            $activities = $activityManager->selectAll();

            return $this->twig->render('Activity/index.html.twig', [
                'activities' => $activities,
                'user' => $_SESSION['user']
                ]);
        }
    }
    /**
     * Show informations for a specific activity
     */
    public function show(): string
    {
        session_start();

        if (!isset($_SESSION["user"])) {
            header('Location: /home/index');
        }

        $activityManager = new ActivityManager();

        $participantsId = (new GatheringManager())->selectAllParticipantsbyActivityId((int)$_GET['id']);

        $participantsName = [];

        foreach ($participantsId as $key) {
            foreach ($key as $participantId) {
                array_push($participantsName, ((new MemberManager())->selectOnlyNameById((int)$participantId)));
            }
        }

        return $this->twig->render('Activity/show.html.twig', [
            'activity' =>
            $activityManager->selectOneAndJoinMemberById($activityManager->selectOneById((int)$_GET['id'])),
            'user' => $_SESSION['user'],
            'participants' => $participantsName
        ]);
    }

    /**
     * Add a new activity
     */
    public function add(): string
    {
        session_start();

        if (!isset($_SESSION["user"])) {
            header('Location: /home/index');
        }

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            array_push($errors, $this->validationService->checkAddFormEmptiness());

            $image = '';

            if ($_POST['activity_type'] === 'sport') {
                $image = '/assets/images/sport.jpg';
            }
            if ($_POST['activity_type'] === 'entertainment') {
                $image = '/assets/images/entertainment.png';
            }
            if ($_POST['activity_type'] === 'culture') {
                $image = '/assets/images/culture.jpeg';
            }

            $hasNotErrors = false;

            foreach ($errors as $error) {
                $error === null ?  $hasNotErrors = true : $hasNotErrors = false;
            }

            if ($hasNotErrors === true) {
                $activity = [
                    'name' => $_POST['title'],
                    'activity_type' => $_POST['activity_type'],
                    'content' => $_POST['content'],
                    'max_registered_members' => $_POST['max_registered_members'],
                    'localisation' => $_POST['localisation'],
                    'start_at' => $_POST['start_at'],
                    'end_at' => $_POST['end_at'],
                    'image' => $image,
                    'member_id' => ($_SESSION['user']['id']),
                ];
                $activityId =  (new ActivityManager())->insert($activity);

                $idArray = [
                    'memberid' => $_SESSION['user']['id'],
                    'activityid' => $activityId
                ];

                (new GatheringManager())->insert($idArray);
                header('Location:/activity/index');
            }
        }
        return $this->twig->render('Activity/add.html.twig', [ 'errors' => $errors]);
    }

    public function join()
    {
        session_start();

        if (!isset($_SESSION["user"])) {
            header('Location: /home/index');
        }

        $_GET = array_map('intval', $_GET);

        (new GatheringManager())->insert($_GET);
        header('Location:/activity/show/?id=' . $_GET['activityid']);
    }

    public function myactivities()
    {
        session_start();

        if (!isset($_SESSION["user"])) {
            return $this->twig->render("Home/index.html.twig");
        }

        if ($_GET['user'] !== $_SESSION['user']['name']) {
            header('Location: /activity/index');
        } else {
            return $this->twig->render('Activity/myactivities.html.twig', [
                'myactivities' => (new ActivityManager())->selectAllByMemberId($_SESSION['user']['id']),
                'joinactivities' => (new GatheringManager())->selectJoinedActivitybymemberId($_SESSION['user']['id']),
            ]);
        }
    }

    public function gallery()
    {
        session_start();

        $apiGet = new ApiGet();

        return $this->twig->render('Activity/gallery.html.twig', [
            'photos' => $apiGet->getPhotoApi('https://api.nasa.gov/mars-photos/api/v1/rovers/curiosity/photos?earth_date=2021-04-30&api_key=sGRiW62hIIGP2B3zRgfyyJ8bJn7qJeFx5lnza8PT'),
        ]);
    }

    public function search()
    {
        session_start();

        return $this->twig->render('Activity/index.html.twig', [
            'activities' => (new ActivityManager())->selectAllByActivityType($_POST['type']),
            'user' => $_SESSION['user']
            ]);
    }
}
