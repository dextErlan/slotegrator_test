<?php

require __DIR__ . '/../vendor/autoload.php';





$request = Laminas\Diactoros\ServerRequestFactory::fromGlobals(
    $_SERVER,
    $_GET,
    $_POST,
    $_COOKIE,
    $_FILES
);


$httpKernel =







echo "Test project started!";


function login($email, $password)
{
    $password = hash($password);
    $user = $userRepository->find([$email, $password]);
    if ($user) {
        return true;
    }

    return false;
}

function getRandomGift()
{
    $giveawayService->getRandomGift();
}

function sendMoneyToBank(){}

function sendPrizeForWinner(){}

function convertMoneyToPoint(){}

function refusePrize(){}

function consoleJobForTransferMoneyToBank(){}

Class MoneyService {
    public $limitInLottery;
    public $limitForUser;

    public function addMoneyForUser(User $user, int $sum){
        //isAllowedAddMoneyForUser($user, $sum)
        //$user->userMoney()->money_in_app += $sum; save();
    }

    public function setTaskForTransferMoney(User $user, int $sum, BankAccount $accountNumber){
        // isUserBankAccount($user, $accountNumber)
        // blockMoney
        // MoneyTransactionService->openTransferTransaction()
    }

    //Проверка принадлежит ли счет пользователю
    public function isUserBankAccount(User $user, BankAccount $accountNumber): bool {}

    public function transferMoney(){
        // Поиск записей в TransferTask со статусом и в цикле по каждой записи
        // isUserBankAccount
        // isFundsAvailableForUser
        // Обращение к апи банка и передача DTO
        // unblockMoney ИЛИ refundMoney
        // MoneyTransactionService->changeTransferTransactionStatus
    }

    public function convertToUserPoints(User $user, int $sum){
        // isFundsAvailableForUser
        // setTaskForConvertToUserPoints
        // конвертация
        // PointService->addPointForUser($points)
        // MoneyTransactionService->changeConvertTransactionStatus
        // unblockMoney ИЛИ refundMoney
    }

    public function setTaskForConvertToUserPoints(User $user, int $sum){
        // blockMoney
        // MoneyTransactionService->openConvertTransaction($user, $sum).
    }

    //снятие денег с блока
    public function unblockMoney(){}

    //Блок переводимой суммы
    public function blockMoney(){}

    //Возврат на счет пользователя, после не успешной транзакции
    public function refundMoney(){}

    //Проверка на лимит
    public function isAllowedAddMoneyForUser(User $user, int $sum){}

    // Проверить доступные средства
    public function isFundsAvailableForUser(User $user, int $sum){}
}

class MoneyTransactionService
{
    // Запись таска на конвертацию MoneyTransactionConvert.
    public function openConvertTransaction(User $user, int $sum){
        //getExchangeRate
        // write to MoneyTransactionConvert
    }

    public function getExchangeRate(){}

    // Запись таска на перевод денег в банк MoneyTransactionToBank.
    public function openTransferTransaction(User $user, int $sum, BankAccount $accountNumber){
        // write to MoneyTransactionToBank
    }

    //Проверка текущего статуса и возможность перехода в новый
    public function changeTransferTransactionStatus(int $transactionId,  $newStatus){}

    //Проверка текущего статуса и возможность перехода в новый
    public function changeConvertTransactionStatus(int $transactionId,  $newStatus){}
}

class PointService
{
    private UserPoint $userPoint;
    public function __construct(User $user){
        $this->userPoint = $user->userPoint();
    }

    public function addPointForUser(int $points){
        $this->userPoint->point += $points;
        // save();
    }
}

class PrizeService
{
    public $limitForUser;

    private UserPrize $userPrize;
    public function __construct(User $user){
        $this->userPrize = $user->userPrize();
    }

    public function addPrizeForUser(Prize $prize){
        if (!$this->isAllowedAddPrizeForUser()) {
            throw new Exception('Достигнут лимит призов у пользователя!');
        }
        $this->userPrize->prize = $prize;
        // save();
    }

    //Проверка на лимит
    public function isAllowedAddPrizeForUser(User $user): bool{
        // Получить кол-во призов у пользователя из записей в БД
        // Сравнить с лимитом
    }

    //Проверка текущего статуса и возможность перехода в новый
    public function changeStatus(UserPrize $userPrize,  $newStatus){}
}