<?php

/**
 * This controller is responsible for the game actions.
 *
 * PHP version 7.4
 *
 * @category   Service
 * @author     Antony Roussos <antrouss4@gmail.com>
 * @version    0.0.1
 */

namespace App\Controller;

use App\Service\BaseService;
use App\Service\GameService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends BaseController
{
    /**
     * Endpoint to create a new game
     *
     * @param GameService $game_service
     * 
     * @return Response
     * 
     * @Route("/game", name="create_game", methods={"POST"})
     */
    public function newGame(GameService $game_service): Response
    {
        $user = $this->getUser();
        $result = $game_service->createGame($user);
        return $this->respond($result);
    }

    /**
     * Endpoint to get a game object.
     *
     * @param GameService $game_service
     * @param int $id the game id
     * 
     * @return Response
     * 
     * @Route("/game/{id}", name="get_game", methods={"GET"})
     */
    public function getGame(GameService $game_service, $id): Response
    {
        $data = (object) [
            'game' => $id,
        ];
        $result = $this->validate($data, $this, __FUNCTION__);
        /**
         * If validation using json schema has errors, then respond with the
         * errors, else continue with user registration.
         */
        if ($result['code'] !== BaseService::SUCCESS) {
            return $this->respond($result);
        }
        $result = $game_service->getGame($id);
        return $this->respond($result);
    }

    /**
     * Endpoint to be used by the current spymaster to give evidence to the other players
     *
     * @param Request $request
     * @param GameService $game_service
     * @param int $id the turn id
     * 
     * @return Response
     * 
     * @Route("/turn/{id}/evidence", name="add_evidence", methods={"PUT"})
     */
    public function addEvidence(Request $request, GameService $game_service, $id): Response
    {
        $user = $this->getUser();
        $data = (object) json_decode($request->getContent(), true);
        $data->turn = $id;
        $result = $this->validate($data, $this, __FUNCTION__);
        /**
         * If validation using json schema has errors, then respond with the
         * errors, else continue with user registration.
         */
        if ($result['code'] !== BaseService::SUCCESS) {
            return $this->respond($result);
        }
        $result = $game_service->addEvidence($user, $data->turn, $data->word, $data->number);
        return $this->respond($result);
    }

    /**
     */
    /**
     * Endpoint to be used by a player(not the spymaster) to guess a word
     *
     * @param Request $request
     * @param GameService $game_service
     * @param int $id the turn id
     * 
     * @return Response
     * 
     * @Route("/turn/{id}/guess", name="add_guess", methods={"POST"})
     */
    public function addGuess(Request $request, GameService $game_service, $id): Response
    {
        $user = $this->getUser();
        $data = (object) json_decode($request->getContent(), true);
        $data->turn = $id;
        $result = $this->validate($data, $this, __FUNCTION__);
        /**
         * If validation using json schema has errors, then respond with the
         * errors, else continue with user registration.
         */
        if ($result['code'] !== BaseService::SUCCESS) {
            return $this->respond($result);
        }
        $result = $game_service->guess($user, $data->turn, $data->guess);
        return $this->respond($result);
    }

    /**
     * Endpoint to be used by a player(not the spymaster) to finish their turn.
     *
     * @param Request $request
     * @param GameService $game_service
     * @param int $id the turn id
     * 
     * @return Response
     * 
     * @Route("/turn/{id}/finish", name="finish_turn", methods={"PUT"})
     */
    public function finishTurn(Request $request, GameService $game_service, $id): Response
    {
        $user = $this->getUser();
        $data = (object) json_decode($request->getContent(), true);
        $data->turn = $id;
        $result = $this->validate($data, $this, __FUNCTION__);
        /**
         * If validation using json schema has errors, then respond with the
         * errors, else continue with user registration.
         */
        if ($result['code'] !== BaseService::SUCCESS) {
            return $this->respond($result);
        }
        $result = $game_service->finishTurn($user, $data->turn);
        return $this->respond($result);
    }

    /**
     * Endpoint to be used from a team member to edit a team.
     *
     * @param Request $request
     * @param GameService $game_service
     * @param $id the team id
     * 
     * @return Response
     * 
     * @Route("/team/{id}", name="edit_team", methods={"PUT"})
     */
    public function editTeam(Request $request, GameService $game_service, $id): Response
    {
        $user = $this->getUser();
        $user_data = json_decode($request->getContent(), true);
        $user_data['team'] = $id;
        $data = (object) $user_data;
        $result = $this->validate($data, $this, __FUNCTION__);
        /**
         * If validation using json schema has errors, then respond with the
         * errors, else continue with user registration.
         */
        if ($result['code'] !== BaseService::SUCCESS) {
            return $this->respond($result);
        }
        $result = $game_service->renameTeam($user, $data->team, $data->name);
        return $this->respond($result);
    }

    /**
     * Endpoint to be used from a user to join a team.
     *
     * @param Request $request
     * @param GameService $game_service
     * 
     * @return Response
     * 
     * @Route("/team/join", name="join_team", methods={"POST"})
     */
    public function joinTeam(Request $request, GameService $game_service): Response
    {
        $user = $this->getUser();
        $user_data = json_decode($request->getContent(), true);
        $data = (object) $user_data;
        $result = $this->validate($data, $this, __FUNCTION__);
        /**
         * If validation using json schema has errors, then respond with the
         * errors, else continue with user registration.
         */
        if ($result['code'] !== BaseService::SUCCESS) {
            return $this->respond($result);
        }
        $result = $game_service->joinTeam($data->team, $user);
        return $this->respond($result);
    }

    /**
     * Endpoint to be used to start a new game.
     *
     * @param Request $request
     * @param GameService $game_service
     * 
     * @return Response
     * 
     * @Route("/game/start", name="start_game", methods={"POST"})
     */
    public function startGame(Request $request, GameService $game_service): Response
    {
        $user_data = json_decode($request->getContent(), true);
        $data = (object) $user_data;
        $result = $this->validate($data, $this, __FUNCTION__);
        /**
         * If validation using json schema has errors, then respond with the
         * errors, else continue with user registration.
         */
        if ($result['code'] !== BaseService::SUCCESS) {
            return $this->respond($result);
        }
        $result = $game_service->startGame($data->game);
        return $this->respond($result);
    }
}
