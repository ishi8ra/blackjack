<?php

namespace Blackjack;

require_once('Player.php');
require_once('PlayerAction.php');
require_once('PlayerBet.php');
require_once('Validator.php');

use Blackjack\Player;
use Blackjack\PlayerAction;
use Blackjack\PlayerBet;
use Blackjack\Validator;

/**
 * ノンプレイヤーキャラクタークラス
 */
class AutoPlayer extends Player implements PlayerAction, PlayerBet
{
    use Validator;

    /**
     * プレイヤーのタイプ別にチップをベットする行動を選択する
     *
     * @return void
     */
    public function bet(): void
    {
        while ($this->getBets() === 0) {
            echo Message::getPlaceYourBetsMessage($this);
            $input = $this->selectBets();
            $error = $this->validateInputBets($input);
            if ($error === '') {
                $this->changeBets($input);
                echo $this->getBets() . 'ドルをベットしました。' . PHP_EOL;
            } else {
                echo $error;
            }
        }
    }

    /**
     * ベットする額を選択する
     *
     * @return string
     */
    public function selectBets(): string
    {
        $max = $this->getChips() > 1000 ? 1000 : $this->getChips();
        $input = rand(1, $max);
        echo $input . PHP_EOL;
        return $input;
    }

    /**
     * 選択したアクション（ヒットかスタンド）により進行する
     *
     * @param Deck $deck
     * @param Dealer $dealer
     * @return void
     */
    public function action(Deck $deck, Dealer $dealer): void
    {
        $message = '';
        while ($this->getStatus() === self::HIT) {
            echo Message::getProgressMessage($this);
            echo Message::getProgressQuestionMessage();
            $inputYesOrNo = $this->selectHitOrStand();

            if ($inputYesOrNo === 'Y') {
                $dealer->dealOneCard($deck, $this);
                $dealer->getJudge()->checkBurst($this);
                $message = Message::getCardDrawnMessage($this);
            } elseif ($inputYesOrNo === 'N') {
                $this->changeStatus(self::STAND);
                $message = PHP_EOL;
            }
            echo $message;
        }
    }

    /**
     * ヒットかスタンドを Y/N で選択する（カードの合計値が17以上になるまで引き続ける）
     *
     * @return string $inputYesOrNo
     */
    public function selectHitOrStand(): string
    {
        if ($this->getScoreTotal() < 17) {
            $inputYesOrNo = 'Y';
            echo 'Y' . PHP_EOL;
        } else {
            $inputYesOrNo = 'N';
            echo 'N' . PHP_EOL;
        }
        return $inputYesOrNo;
    }
}
