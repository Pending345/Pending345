<?php

require "vendor/autoload.php";

use Smarty\Smarty;

use Calendar\UserList;
use Calendar\User;
use Calendar\Event;
use Calendar\Calendar;

// Start session
session_start();

$_SESSION["userList"] = $_SESSION['userList'] ?? new UserList();
//$_SESSION["calendar"] = $_SESSION["calendar"] ?? new Calendar();

// Smarty setup
$template = new Smarty();
$template->setTemplateDir('templates');
$template->setCompileDir('templates_c');

$template->assign('currentUser', $_SESSION['currentUser'] ?? null);

switch ($_GET['page'] ?? "home") {
    case 'home':
        $template->display('home.tpl');
        break;
    case "loginForm":
        $template->display('login.tpl');
        break;
    case "login":
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $inputs = ["username", "password"];
            $required = [];
            foreach ($inputs as $input) {
                if (empty($_POST[$input])) {
                    $required[] = ucfirst($input);
                }
            }
            if (!empty($required)) {
                $error = "Please fill in the following fields: " . implode(", ", $required) . ".";
                $template->assign('error', $error);
                $template->display('login.tpl');
            } else {
                $user = $_SESSION["userList"]->getUser($_POST["username"]);
                if ($user instanceof User) {
                    if ($user->checkPassword($_POST["password"])) {
                        $_SESSION["currentUser"] = $user;
                        $template->assign('currentUser', $user);
                        $template->display('home.tpl');
                    } else {
                        $template->assign('error', "Wrong password.");
                        $template->display('login.tpl');
                    }
                } else {
                    $template->assign('error', $user);
                    $template->display('login.tpl');
                }
            }
        }
        break;
    case "signupForm":
        $template->display('signup.tpl');
        break;
    case "signup":
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $inputs = ["username", "email", "password", "repeatPassword"];
            $required = [];
            foreach ($inputs as $input) {
                if (empty($_POST[$input])) {
                    $required[] = ucfirst($input);
                }
            }
            if (!empty($required)) {
                $error = "Please fill in the following fields: " . implode(", ", $required) . ".";
                $template->assign('error', $error);
                $template->display('signup.tpl');
            } else {
                if ($_POST['password'] !== $_POST['repeatPassword']) {
                    $template->assign('error', "Passwords do not match.");
                    $template->display('signup.tpl');
                } else {
                    if ($_SESSION['userList']->getUserByEmail($_POST['email']) instanceof User) {
                        $template->assign('error', "Email already in use.");
                        $template->display('signup.tpl');
                        break;
                    }
                    $user = new User();
                    $user->setUser($_POST['username'], $_POST['email'], $_POST['password']);
                    $_SESSION["userList"]->addUser($user);
                    $_SESSION["currentUser"] = $user;
                    $template->assign('currentUser', $user);
                    $template->display('home.tpl');
                }
            }
        }
        break;
    case "logout":
        unset($_SESSION["currentUser"]);
        $template->assign('currentUser', null);
        $template->display('home.tpl');
        break;
    case "addEvent":
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $inputs = ["title", "description", "date", "time"];
            $required = [];
            foreach ($inputs as $input) {
                if (empty($_POST[$input])) {
                    $required[] = ucfirst($input);
                }
            }
            if (!empty($required)) {
                $error = "Please fill in the following fields: " . implode(", ", $required) . ".";
                $template->assign('error', $error);
                $template->display('home.tpl');
            } else {
                if (isset($_POST["repeating"])) {
                    $time = strtotime($_POST['date'] . $_POST['time']);
                    $weekStep = ($_POST["repeatType"] === "week") ? 7 : 0;
                    $monthStep = ($_POST["repeatType"] === "month") ? 1 : 0;
                    $yearStep = ($_POST["repeatType"] === "year") ? 1 : 0;
                    while ($time <= strtotime($_POST["endDate"])) {
                        $time = mktime(date("H", $time), date("i", $time), date("s", $time),
                            (int)date("m", $time) + $monthStep, (int)date("d", $time) + $weekStep, (int)date("Y", $time) + $yearStep);
                        $event = new Event();
                        $event->setEvent($_POST['title'], $_POST['description'], $time, $_POST['color']);
                        $_SESSION["currentUser"]->getCalendar()->addEvent($event);
                    }
                }
                $event = new Event();
                $date = strtotime($_POST['date'] . $_POST['time']);
                $event->setEvent($_POST['title'], $_POST['description'], $date, $_POST['color']);
                $_SESSION["currentUser"]->getCalendar()->addEvent($event);
                $returnUrl = $_POST['returnUrl'] ?? 'index.php?page=home';
                header("Location: $returnUrl");
            }
        }
        break;
    case "updateEvent":
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $event = $_SESSION["currentUser"]->getCalendar()->getEventById($_POST["id"]);
            if (isset($_POST["delete"])) {
                if ($event instanceof Event) {
                    $_SESSION["currentUser"]->getCalendar()->removeEvent($event);
                    $returnUrl = $_POST['returnUrl'] ?? 'index.php?page=home';
                    header("Location: $returnUrl");
                    break;
                }
            }
            $inputs = ["title", "description", "date", "time"];
            $required = [];
            foreach ($inputs as $input) {
                if (empty($_POST[$input])) {
                    $required[] = ucfirst($input);
                }
            }
            if (!empty($required)) {
                $error = "Please fill in the following fields: " . implode(", ", $required) . ".";
                $template->assign('error', $error);
                $template->display('home.tpl');
            } else {
                $date = strtotime($_POST['date'] . $_POST['time']);
                $event->setEvent($_POST['title'], $_POST['description'], $date, $_POST['color']);
                $returnUrl = $_POST['returnUrl'] ?? 'index.php?page=home';
                header("Location: $returnUrl");
            }
        }
        break;
    case "shareEvent":
        if (!isset($_SESSION["currentUser"])) {
            $template->assign("error", "Please login to view your calendar.");
            $template->display('login.tpl');
            break;
        }
        $user = $_SESSION["userList"]->getUserByEmail(htmlspecialchars_decode($_GET["origin"]));
        $event = $user->getCalendar()->getEventById($_GET["event"]);
        if ($event instanceof Event) {
            if ($_SESSION["currentUser"] !== $user) {
                $_SESSION["currentUser"]->getCalendar()->addEvent($event);
            }
        }
        $template->display('home.tpl');
        break;
    case "shareCalendar":
        if (!isset($_SESSION["currentUser"])) {
            $template->assign("error", "Please login to view your calendar.");
            $template->display('login.tpl');
            break;
        }
        $user = $_SESSION["userList"]->getUserByEmail(htmlspecialchars_decode($_GET["origin"]));
        $event = $user->getCalendar()->getEvents();
        if ($_SESSION["currentUser"] !== $user) {
            $_SESSION["currentUser"]->getCalendar()->addEvent($event);
        }
        $template->display('home.tpl');
        break;
    case "calendarMonth":
        if (!isset($_SESSION["currentUser"])) {
            $template->assign("error", "Please login to view your calendar.");
            $template->display('login.tpl');
            break;
        }
        $referenceTime = $_GET["time"] ?? time();
        $template->assign("calendarHTML", $_SESSION["currentUser"]->getCalendar()->generateMonth($referenceTime));
        $template->assign("date", date("F - Y", $referenceTime));
        $template->assign("nextMonth", mktime(0, month: (int)date("m", $referenceTime) + 1, year: date("Y", $referenceTime)));
        $template->assign("previousMonth", mktime(0, month: date("m", $referenceTime) - 1, year: date("Y", $referenceTime)));
        $template->assign("returnTo", htmlspecialchars($_SERVER['REQUEST_URI'] ?? "index.php?page=home"));

        $template->display('calendarMonth.tpl');
        break;
    case "calendarWeek":
        if (!isset($_SESSION["currentUser"])) {
            $template->assign("error", "Please login to view your calendar.");
            $template->display('login.tpl');
            break;
        }
        $referenceTime = $_GET["time"] ?? time();
        $template->assign("calendarHTML", $_SESSION["currentUser"]->getCalendar()->generateWeek($referenceTime));
        $template->assign("date", date("l d F Y", $referenceTime));
        $template->assign("month", date("F - Y", $referenceTime));
        $template->assign("nextWeek", mktime(0, month: date("m", $referenceTime), day: (int)date("d", $referenceTime) + 7, year: date("Y", $referenceTime)));
        $template->assign("previousWeek", mktime(0, month: date("m", $referenceTime), day: date("d", $referenceTime) - 7, year: date("Y", $referenceTime)));
        $template->assign("returnTo", htmlspecialchars($_SERVER['REQUEST_URI'] ?? "index.php?page=home"));
        $template->display('calendarWeek.tpl');
        break;
    case "calendarDay":
        if (!isset($_SESSION["currentUser"])) {
            $template->assign("error", "Please login to view your calendar.");
            $template->display('login.tpl');
            break;
        }
        $referenceTime = $_GET["time"] ?? time();
        $template->assign("calendarHTML", $_SESSION["currentUser"]->getCalendar()->generateDay($referenceTime));
        $template->assign("date", date("l d F Y", $referenceTime));
        $template->assign("nextDay", mktime(0, month: date("m", $referenceTime), day: (int)date("d", $referenceTime) + 1, year: date("Y", $referenceTime)));
        $template->assign("previousDay", mktime(0, month: date("m", $referenceTime), day: date("d", $referenceTime) - 1, year: date("Y", $referenceTime)));
        $template->assign("returnTo", htmlspecialchars($_SERVER['REQUEST_URI'] ?? "index.php?page=home"));
        $template->display('calendarDay.tpl');
        break;
    default:
        $template->display('layout.tpl');
        break;
}