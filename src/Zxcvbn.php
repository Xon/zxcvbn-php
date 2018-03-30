<?php

namespace ZxcvbnPhp;

/**
 * The main entry point.
 *
 * @see  zxcvbn/src/main.coffee
 */
class Zxcvbn
{
    /**
     * @var
     */
    protected $matcher;

    /**
     * @var
     */
    protected $scorer;

    /**
     * @var
     */
    protected $timeEstimator;

    /**
     * @var
     */
    protected $feedback;

    public function __construct()
    {
        $this->matcher = new \ZxcvbnPhp\Matcher();
        $this->scorer = new \ZxcvbnPhp\Scorer();
        $this->timeEstimator = new \ZxcvbnPhp\TimeEstimator();
        $this->feedback = new \ZxcvbnPhp\Feedback();
    }

    /**
     * Calculate password strength via non-overlapping minimum entropy patterns.
     *
     * @param string $password
     *   Password to measure.
     * @param array $userInputs
     *   Optional user inputs.
     *
     * @return array
     *   Strength result array with keys:
     *     password
     *     entropy
     *     match_sequence
     *     score
     */
    public function passwordStrength($password, array $userInputs = array())
    {
        $timeStart = microtime(true);

        $sanitizedInputs = array_map(
            function($input) {
                return strtolower((string) $input);
            },
            $userInputs
        );

        // Get matches for $password.
        // Although the coffeescript upstream sets $sanitizedInputs as a property,
        // doing this immutably makes more sense and is a bit easier
        $matches = $this->matcher->getMatches($password, $sanitizedInputs);

        // 1.0 rewrite: Although upstream has a single variable for $result,
        // this is opaque and I'd rather do it a clearer, more transparent way
        $result = $this->scorer->mostGuessableMatchSequence($password, $matches);
        $attackTimes = $this->timeEstimates->estimateAttackTimes($result['guesses']);

        $feedback = $this->feedback->getFeedback($result['score'], $result['sequence']);

        return array_merge(
            $result,
            $attackTimes,
            array(
                'feedback'  => $feedback,
                'calc_time' => microtime(true) - $timeStart
            )
        );
    }
}