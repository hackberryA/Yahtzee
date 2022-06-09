<?php
/** Categories And Scores **/
// Aces, Twos, Threes, Fours, Fives, Sixes (The sum of dice with the number)
// Three of a kind, Four of a kind (Sum of all dice)
// Full House(25), Small Straight(30), Large Straight(40), Yahtzee(50)
// Chance(Sum of all dice)
$y = new Yahtzee();
$y->exec();

class Yahtzee {
	/** スコア */
	private $scores = [
		'Aces' => false, 'Twos' => false, 'Threes' => false,
		'Fours' => false, 'Fives' => false, 'Sixes' => false,
		'Three Of A Kind' => false, 'Four Of A Kind' => false,
		'Full House' => false, 'Small Straight' => false, 'Large Straight' => false,
		'Yahtzee' => false, 'Chance' => false, 'Bonus' => false,
	];

	/**
	 * 実行（デフォルトは13ラウンド）
	 */
	public function exec($round = 13){
		for($i=1;$i<=13;$i++) $this->roundYahtzee();
	}

	/**
	 * 1ラウンド分の処理
	 */
	function roundYahtzee() {
		// ランダムな5値
		$dice = array_map(fn()=>random_int(1,6),range(1,5));
		$cnt = 1;
		while(true) {
			echo "Dice: ".implode(' ', $dice).PHP_EOL;

			// ---- 1stチェック ----
			$result = $this->checkYahtzee($dice);

			echo "[Upper Section] ";
			foreach ($result as $k => $v) {
				echo "$k: $v, ";
				if ($k=="Sixes") {
					echo PHP_EOL;
					echo "[Lower Section] ";
				}
			}
			echo PHP_EOL;
			while(true) {
				echo "Select (Example: Aces, Small Straight, or 134, 12345): ";
				$select = trim(fgets(STDIN));
				if (isset($this->scores[$select]) && $this->scores[$select] === false) {
					$this->scores[$select] = $result[$select];
					break 2;
				} else if ($cnt < 3 && is_numeric($select) && $select > 0) {
					// 一部ダイスふり直し
					$select = str_split($select);
					foreach($select as $v) {
						if ($v > 5) continue;
						$dice[$v-1] = random_int(1,6);
					}
					$cnt++;
					break;
				}
			}
		}

		echo "----------------------------------------------".PHP_EOL;
		echo "[Upper Section] ";
		foreach ($this->scores as $k => $v) {
			echo "$k: $v, ";
			if ($k=="Sixes") {
				echo PHP_EOL;
				echo "[Lower Section] ";
			}
		}
		// ボーナス判定（Upper Sectionの合計得点が63点以上の場合、ボーナスとして35点が加算される）
		if ($this->scores['Bonus'] === false) {
			$up_sum = $this->scores['Aces']
					+ $this->scores['Twos']
					+ $this->scores['Threes']
					+ $this->scores['Fours']
					+ $this->scores['Fives']
					+ $this->scores['Sixes'];
			if ($up_sum >= 63) {
				$this->socres['Bonus'] = 35;
			}
		}
		echo PHP_EOL;
		echo "Current Score: " . array_sum($this->scores).PHP_EOL;
		echo "##############################################".PHP_EOL;
	}

	function checkYahtzee($dice) {
		$sum = array_sum($dice);
		$acv = array_count_values($dice);
		$pair = [1=>0,2=>0,3=>0,4=>0,5=>0];
		foreach ($acv as $v) $pair[$v]++;

		/**
		 * Upper section
		 */
		// 1～6
		$scores['Aces']   = ($acv[1]??0) * 1;
		$scores['Twos']   = ($acv[2]??0) * 2;
		$scores['Threes'] = ($acv[3]??0) * 3;
		$scores['Fours']  = ($acv[4]??0) * 4;
		$scores['Fives']  = ($acv[5]??0) * 5;
		$scores['Sixes']  = ($acv[6]??0) * 6;

		/**
		 * Lower section
		 */
		$scores['Three Of A Kind'] = (($pair[3]==1 || $pair[4]==1 || $pair[5]==1) ? $sum : 0);
		$scores['Four Of A Kind']  = (($pair[4]==1 || $pair[5]==1) ? $sum : 0);
		$scores['Full House']      = ($pair[2]==1 && $pair[3] == 1 ? 25 : 0);
		$scores['Small Straight']  = $this->checkStraight($dice, [1,2,3,4], [2,3,4,5], [3,4,5,6]) ? 30 : 0;
		$scores['Large Straight']  = $this->checkStraight($dice, [1,2,3,4,5], [2,3,4,5,6]) ? 40 : 0;
		$scores['Yahtzee']         = ($pair[5]==1 ? 50 : 0);
		$scores['Chance']          = $sum;

		return $scores;
	}

	/**
	 * dice ⊇ straight
	 */
	function checkStraight($dice, ...$straight) {
		foreach($straight as $s) {
			$ret = array_diff($s, $dice);
			if (empty($ret)) return true;
		}
		return false;
	}
}
