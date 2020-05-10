<?php

/**
 * This class is responsible for the game actions.
 *
 * PHP version 7.4
 *
 * @category   Service
 * @author     Antony Roussos <antrouss4@gmail.com>
 * @version    0.0.1
 */

namespace App\Service;

use App\Entity\Game;
use App\Entity\Team;
use App\Entity\Turn;
use App\Entity\User;
use App\Entity\Word;
use App\Entity\Round;
use App\Repository\GameRepository;
use App\Repository\TeamRepository;
use App\Repository\TurnRepository;
use Doctrine\ORM\EntityManagerInterface;

class GameService extends BaseService
{
    /**
     * @var EntityManagerInterface
     */
    private $doctrine;

    /**
     * @var ZMQService
     */
    private $zmq_service;

    /**
     * @param EntityManagerInterface $doctrine
     */
    public function __construct(EntityManagerInterface $doctrine, ZMQService $zmq_service)
    {
        $this->doctrine = $doctrine;
        $this->zmq_service = $zmq_service;
    }

    /**
     * This method creates a new codenames game.
     * 
     * This method is used to create a new game object. This object will have
     * 2 teams and a host user.
     *
     * @param User $user
     * 
     * @return array
     */
    public function createGame(User $user): array
    {
        $team1 = new Team();
        $team1->setName('Team 1');
        $this->doctrine->persist($team1);
        $team2 = new Team();
        $team2->setName('Team 2');
        $this->doctrine->persist($team2);
        $game = new Game();
        $game->setHost($user);
        $game->addTeam($team1);
        $game->addTeam($team2);
        $game->setStatus(Game::STATUS_CREATED);
        $this->doctrine->persist($game);
        $this->doctrine->flush();
        $game_data = [
            'game' => $game->getId(),
            'status' => 'created',
        ];
        $this->zmq_service->send('searching', $game_data);
        return [
            'code' => self::SUCCESS,
            'data' => $game,
        ];
    }

    /**
     * This method is used for renaming a team.
     *
     * @param User $user
     * @param integer $team_id
     * @param string $name
     * 
     * @return array
     */
    public function renameTeam(User $user, int $team_id, string $name): array
    {
        /**
         * @var TeamRepository
         */
        $team_repo = $this->doctrine->getRepository(Team::class);
        $team = $team_repo->find($team_id);
        if (is_null($team)) {
            return [
                'code' => self::NOT_FOUND_ERR,
                'data' => [
                    'message' => 'Team not found.',
                ],
            ];
        }
        if (!$team->getUsers()->contains($user)) {
            return [
                'code' => self::FORBIDDEN_ERR,
                'data' => [
                    'message' => 'You don\'t belong to that team. You are not authorized to modify it',
                ],
            ];
        }
        $team->setName($name);
        $this->doctrine->persist($team);
        $this->doctrine->flush();
        return [
            'code' => self::SUCCESS,
            'data' => $team,
        ];
    }

    /**
     * Returns available games
     *
     * @return array
     */
    public function getAvailable(): array
    {
        /**
         * @var GameRepository
         */
        $game_repo = $this->doctrine->getRepository(Game::class);
        $games = $game_repo->findBy(['status' => Game::STATUS_CREATED]);
        return [
            'code' => self::SUCCESS,
            'data' => $games,
        ];
    }

    /**
     * This method to make a user able to join a team.
     *
     * @param integer $team_id
     * @param User $user
     * 
     * @return array
     */
    public function joinTeam(int $team_id, User $user): array
    {
        /**
         * @var TeamRepository
         */
        $team_repo = $this->doctrine->getRepository(Team::class);
        $team = $team_repo->find($team_id);
        if (is_null($team)) {
            return [
                'code' => self::NOT_FOUND_ERR,
                'data' => [
                    'message' => 'Team not found.',
                ],
            ];
        }
        $teams = $team->getGame()->getTeams();
        foreach ($teams as $t) {
            $t->removeUser($user);
        }
        $team->addUser($user);
        $this->doctrine->persist($team);
        $this->doctrine->flush();

        return [
            'code' => self::SUCCESS,
            'data' => $team,
        ];
    }

    /**
     * This method makes a game to start.
     *
     * This method creates an array with the words that are down, a map for the
     * spy masters and an empty array (filled with zeros) for the game progress.
     * Additionally it sets the status of the game to in progress.
     * 
     * @param integer $game_id
     * 
     * @return array
     */
    public function startGame(int $game_id): array
    {
        /**
         * @var GameRepository
         */
        $game_repo = $this->doctrine->getRepository(Game::class);
        $game = $game_repo->find($game_id);
        if (is_null($game)) {
            return [
                'code' => self::NOT_FOUND_ERR,
                'data' => [
                    'message' => 'Game not found.',
                ],
            ];
        }
        $teams = $game->getTeams();
        $team1 = $teams->get(0);
        $team2 = $teams->get(1);
        if ($team1->getUsers()->count() != $team2->getUsers()->count()) {
            return [
                'code' => self::VALIDATION_ERR,
                'data' => [
                    'message' => 'The teams should have the same number of players.',
                ],
            ];
        }
        $team1->setNumberOfCards(6);
        $team2->setNumberOfCards(6);
        $round = $this->createRound($game);
        $game->addRound($round);
        $turn = $this->createTurn($round);
        $round->addTurn($turn);
        $game->setStatus(Game::STATUS_IN_PROGRESS);
        $this->doctrine->persist($game);
        $this->doctrine->flush();
        $game_data = [
            'game' => $game->getId(),
            'status' => 'started',
        ];
        $this->zmq_service->send('searching', $game_data);

        return [
            'code' => self::SUCCESS,
            'data' => $game,
        ];
    }

    /**
     * This method is used to get a game by its id.
     *
     * @param integer $game_id
     * 
     * @return array
     */
    public function getGame(int $game_id): array
    {
        /**
         * @var GameRepository
         */
        $game_repo = $this->doctrine->getRepository(Game::class);
        $game = $game_repo->find($game_id);
        if (is_null($game)) {
            return [
                'code' => self::NOT_FOUND_ERR,
                'data' => [
                    'message' => 'Game not found.',
                ],
            ];
        }
        return [
            'code' => self::SUCCESS,
            'data' => $game,
        ];
    }

    /**
     * This method is used to give the spymaster the ability to provide evidence
     * to the other players (a word and a number) to help them find the words
     * of their team.
     *
     * @param User $user the spymaster
     * @param integer $turn_id
     * @param string $word
     * @param integer $number
     * 
     * @return array
     */
    public function addEvidence(User $user, int $turn_id, string $word, int $number): array
    {
        /**
         * @var TurnRepository
         */
        $turn_repo = $this->doctrine->getRepository(Turn::class);
        $turn = $turn_repo->find($turn_id);
        if (is_null($turn)) {
            return [
                'code' => self::NOT_FOUND_ERR,
                'data' => [
                    'message' => 'Turn not found.',
                ],
            ];
        }
        if ($turn->getSpyMaster() != $user) {
            return [
                'code' => self::VALIDATION_ERR,
                'data' => [
                    'message' => 'You are not the spymaster in this round.',
                ],
            ];
        }
        if ($turn->getStatus() === Turn::STATUS_FINISHED) {
            return [
                'code' => self::VALIDATION_ERR,
                'data' => [
                    'message' => 'This turn has already finished.',
                ],
            ];
        }
        if ($turn->getStatus() === Turn::STATUS_IN_PROGRESS) {
            return [
                'code' => self::VALIDATION_ERR,
                'data' => [
                    'message' => 'You have already given the evidence.',
                ],
            ];
        }
        $turn->setWord($word);
        $turn->setNumber($number);
        $turn->setStatus(Turn::STATUS_IN_PROGRESS);
        $this->doctrine->persist($turn);
        $this->doctrine->flush();
        return [
            'code' => self::SUCCESS,
            'data' => $turn,
        ];
    }

    /**
     * This method gives the players who are not the spymasters the ability
     * go guess the words that they think that the spymaster describes.
     *
     * @param User $user
     * @param integer $turn_id
     * @param string $guess
     * 
     * @return array
     */
    public function guess(User $user, int $turn_id, string $guess): array
    {
        /**
         * @var TurnRepository
         */
        $turn_repo = $this->doctrine->getRepository(Turn::class);
        $turn = $turn_repo->find($turn_id);
        if (is_null($turn)) {
            return [
                'code' => self::NOT_FOUND_ERR,
                'data' => [
                    'message' => 'Turn not found.',
                ],
            ];
        }
        if ($turn->getStatus() !== Turn::STATUS_IN_PROGRESS) {
            return [
                'code' => self::NOT_FOUND_ERR,
                'data' => [
                    'message' => 'You can\'t guess for this turn right now.',
                ],
            ];
        }
        $spy_master = $turn->getSpyMaster();
        if ($spy_master === $user) {
            return [
                'code' => self::FORBIDDEN_ERR,
                'data' => [
                    'message' => 'Spymaster can\'t guess.',
                ],
            ];
        }
        $round = $turn->getRound();
        $game = $round->getGame();
        if (!$this->inSameTeam($game, $user, $spy_master)) {
            return [
                'code' => self::FORBIDDEN_ERR,
                'data' => [
                    'message' => 'You are not in the same team with the spymaster.',
                ],
            ];
        }
        $coordinates = explode(',', $guess);
        if (
            count($coordinates) != 2 ||
            $coordinates[0] < 0 ||
            $coordinates[0] > 4 ||
            $coordinates[1] < 0 ||
            $coordinates[1] > 4
        ) {
            return [
                'code' => self::VALIDATION_ERR,
                'data' => [
                    'message' => 'Invalid coordinates.',
                ],
            ];
        }
        if (!$this->canGuessMore($turn)) {
            return [
                'code' => self::CONFLICT_ERR,
                'data' => [
                    'message' => 'You can\'t make more guesses in this round.',
                ],
            ];
        }
        $progress = $round->getProgress();
        if ($progress[$coordinates[0]][$coordinates[1]] === 1) {
            return [
                'code' => self::CONFLICT_ERR,
                'data' => [
                    'message' => 'Someone else has made this guess before.',
                ],
            ];
        }
        $team = $this->getCurrentTeam($game, $user);
        $progress[$coordinates[0]][$coordinates[1]] = $team->getId();
        $round->setProgress($progress);
        $pointed = $turn->getPointed();
        $pointed[$coordinates[0]][$coordinates[1]] = 1;
        $turn->setPointed($pointed);
        $this->doctrine->persist($turn);
        $this->doctrine->persist($round);
        $this->doctrine->flush();
        $map = $round->getMap();
        if ($map[$coordinates[0][$coordinates[1]]] == 'b') {
            $opponent = $this->getOpponent($game, $team);
            $turn->setStatus(Turn::STATUS_FINISHED);
            $round->setStatus(Round::STATUS_FINISHED);
            $round->setWinner($opponent);
            $this->doctrine->persist($round);
            $this->doctrine->persist($turn);
            $this->doctrine->flush();
            return [
                'code' => self::SUCCESS,
                'data' => [
                    'message' => 'You found the bomb. Your team lost the round!',
                ],
            ];
        }

        $winner = $this->checkForWinner($round, $team);
        if (!is_null($winner)) {
            $turn->setStatus(Turn::STATUS_FINISHED);
            $round->setStatus(Round::STATUS_FINISHED);
            $round->setWinner($winner);
            $this->doctrine->persist($round);
            $this->doctrine->persist($turn);
            $this->doctrine->flush();
            if ($winner == $team) {
                return [
                    'code' => self::SUCCESS,
                    'data' => [
                        'message' => "Your team won the round!",
                        'status' => 'win'
                    ],
                ];
            }
            return [
                'code' => self::SUCCESS,
                'data' => [
                    'message' => "Your team lost the round!",
                    'status' => 'lose',
                ],
            ];
        }

        return [
            'code' => self::SUCCESS,
            'data' => $turn,
        ];
    }

    /**
     * This method returns a team's opponent team for a certain game.
     *
     * @param Game $game
     * @param Team $team
     * 
     * @return Team
     */
    private function getOpponent(Game $game, Team $team): Team
    {
        $teams = $game->getTeams();
        foreach ($teams as $t) {
            if ($team !== $t) {
                return $t;
            }
        }
    }

    /**
     * This method returns a team object (winner) if the team has found all
     * the teams corresponding words in the table.
     *
     * @param Round $round
     * 
     * @return Team the winner
     */
    private function checkForWinner(Round $round): Team
    {
        $progress = $round->getProgress();
        $map = $round->getMap();
        foreach ($progress as $row_key => $row) {
            foreach ($row as $column_key => $value) {
                if ($value !== 0) {
                    $map[$row_key][$column_key] = 'x';
                }
            }
        }
        $teams = $round->getGame()->getTeams();
        foreach ($teams as $team) {
            if ($this->countBoardOccurances($map, $team->getId()) == 0) {
                return $team;
            }
        }
        return null;
    }

    /**
     * This method checks if the team can guess more in this round.
     * A team can guess one more word than the spymaster' s number.
     *
     * @param Turn $turn
     * @return boolean
     */
    private function canGuessMore(Turn $turn)
    {
        $can_guess = $turn->getNumber() + 1;
        $pointed_num = $this->countBoardOccurances($turn->getPointed(), 1);
        if ($pointed_num === $can_guess) {
            return false;
        }
        return true;
    }

    /**
     * This method gets a board and a search value, and counts the number of
     * the occurances of the selected value in this array.
     *
     * @param array $board
     * @param mixed $search
     * @return int
     */
    private function countBoardOccurances(array $board, $search): int
    {
        $occurances = 0;
        foreach ($board as $row) {
            foreach ($row as $value) {
                if ($value == $search) {
                    $occurances++;
                }
            }
        }
        return $occurances;
    }

    /**
     * This method is used by the players (not the spymaster) to finish their
     * turn.
     *
     * @param User $user
     * @param integer $turn_id
     * 
     * @return array
     */
    public function finishTurn(User $user, int $turn_id): array
    {
        /**
         * @var TurnRepository
         */
        $turn_repo = $this->doctrine->getRepository(Turn::class);
        $turn = $turn_repo->find($turn_id);
        if (is_null($turn)) {
            return [
                'code' => self::NOT_FOUND_ERR,
                'data' => [
                    'message' => 'Turn not found.',
                ],
            ];
        }
        if ($turn->getStatus() !== Turn::STATUS_IN_PROGRESS) {
            return [
                'code' => self::NOT_FOUND_ERR,
                'data' => [
                    'message' => 'This turn is not in progress.',
                ],
            ];
        }
        $spy_master = $turn->getSpyMaster();
        if ($spy_master === $user) {
            return [
                'code' => self::FORBIDDEN_ERR,
                'data' => [
                    'message' => 'Spymaster can\'t finish a turn.',
                ],
            ];
        }
        $round = $turn->getRound();
        $game = $round->getGame();
        if (!$this->inSameTeam($game, $user, $spy_master)) {
            return [
                'code' => self::FORBIDDEN_ERR,
                'data' => [
                    'message' => 'It is not your team playing in this turn.',
                ],
            ];
        }
        $turn->setStatus(Turn::STATUS_FINISHED);
        $this->doctrine->persist($turn);
        $this->doctrine->flush();

        return [
            'code' => self::SUCCESS,
            'data' => $turn,
        ];
    }

    /**
     * This method checks if 2 users are in the same team in the game
     *
     * @param Game $game
     * @param User $user1
     * @param User $user2
     * 
     * @return boolean
     */
    private function inSameTeam(Game $game, User $user1, User $user2): bool
    {
        return $this->getCurrentTeam($game, $user1) === $this->getCurrentTeam($game, $user2);
    }

    /**
     * Returns the current team of the given user in the given game
     *
     * @param Game $game
     * @param User $user
     * 
     * @return Team|null
     */
    private function getCurrentTeam(Game $game, User $user): ?Team
    {
        $teams = $game->getTeams();
        foreach ($teams as $team) {
            if ($team->getUsers->contains($user)) {
                return $team;
            }
        }
        return null;
    }

    /**
     * This method creates a new turn for the given round.
     *
     * @param Round $round
     * 
     * @return Turn
     */
    private function createTurn(Round $round): Turn
    {
        $spy_master_1 = $round->getSpyMaster1();
        $spy_master_2 = $round->getSpyMaster2();
        $spy_masters_order = [$spy_master_1, $spy_master_2];
        if ($round->getStartingTeam()->getUsers()->contains($spy_master_2)) {
            $spy_masters_order = array_reverse($spy_masters_order);
        }
        $current_spy_master = $spy_masters_order[0];
        if ($round->getTurns()->count() % 2 != 0) {
            $current_spy_master = $spy_masters_order[1];
        }
        $turn = new Turn();
        $turn->setRound($round);
        $turn->setSpyMaster($current_spy_master);
        $turn->setStatus(Turn::STATUS_CREATED);
        $turn->setPointed($this->createBasicBoard());
        $this->doctrine->persist($turn);
        $this->doctrine->flush();

        return $turn;
    }

    /**
     * This method creates a new round in the given game.
     *
     * @param Game $game
     * 
     * @return Round
     */
    private function createRound(Game $game): Round
    {
        $users_per_team = $game->getTeams()->get(0)->getUsers()->count();
        $users_turn = $game->getRounds()->count() % $users_per_team;
        $team1 = $game->getTeams()->get(0);
        $team2 = $game->getTeams()->get(1);
        $spy_master1 = $team1->getUsers()->get($users_turn);
        $spy_master2 = $team2->getUsers()->get($users_turn);
        $team1->setNumberOfCards(6);
        $team2->setNumberOfCards(6);
        $teams = [$team1, $team2];
        $starting_team = array_rand($teams);
        $round = new Round();
        $round->setStartingTeam($teams[$starting_team]);
        /** Starting team has one more card to find */
        $teams[$starting_team]->setNumberOfCards(7);
        $round->setWords($this->createWordsBoard());
        $round->setMap($this->createMap($team1, $team2));
        $round->setProgress($this->createBasicBoard());
        $round->setStatus(Game::STATUS_IN_PROGRESS);
        $round->setSpyMaster1($spy_master1);
        $round->setSpyMaster2($spy_master2);
        $round->setGame($game);
        $this->doctrine->persist($round);
        $this->doctrine->flush();
        return $round;
    }

    /**
     * This method creates a board with random words.
     *
     * @return array a 2 dimensional 5*5 array with the word ids.
     */
    private function createWordsBoard(): array
    {
        $word_repo = $this->doctrine->getRepository(Word::class);
        $words = $word_repo->findAll();
        $shuffled_words = array_rand($words, 25);
        $word_ids = [];
        foreach ($shuffled_words as $word) {
            $word_ids[] = $words[$word]->getId();
        }

        return $this->createRandomBoard($shuffled_words);
    }

    /**
     * This method creates a random map.
     *
     * @param Team $team1
     * @param Team $team2
     * @return array
     */
    private function createMap(Team $team1, Team $team2): array
    {
        $number_of_options = 25;
        $options = [];
        $options[] = 'b'; // bomb
        for ($i = 0; $i < $team1->getNumberOfCards(); $i++) {
            $options[] = $team1->getId(); // team 1
        }
        for ($i = 0; $i < $team2->getNumberOfCards(); $i++) {
            $options[] = $team2->getId(); // team 2
        }
        for ($i = count($options); $i < $number_of_options; $i++) {
            $options[] = 'c'; // citizen
        }
        return $this->createRandomBoard($options);
    }

    /**
     * Creates a 2 dimension 5*5 array filled randomly with the options passed.
     *
     * @param array $options an array with 25 values to fill the 2 dimension array
     * 
     * @return array|null
     */
    private function createRandomBoard(array $options): ?array
    {
        if (count($options) != 25) {
            return null;
        }
        $board = $this->createBasicBoard();
        shuffle($options);
        foreach ($board as $row_key => $row) {
            foreach ($row as $column_key => $column) {
                $board[$row_key][$column_key] = array_pop($options);
            }
        }
        return $board;
    }

    /**
     * Creates a 2 dimension 5*5 array filled with zeros.
     *
     * @return array
     */
    private function createBasicBoard(): array
    {
        return [
            [0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0],
            [0, 0, 0, 0, 0],
        ];
    }
}
