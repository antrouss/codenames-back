
# Codenames specifications

This document describes the game codenames contents, the elements that it includes and how the whole application works

## Definitions
**Game**: A game is considered the whole "match" between two teams. A game starts the time 2 teams are formed, and ends the time that they decide to finish or change teams.
**Round**: A round is considered every time new cards and maps are generated and the two codemaster try to give clues to their team members.
**Turn**: A turn is considered every time a codemaster gives a clue, and their teammates are trying to find the words that the codemaster tries to describe.

## Pages
- Register page
- Login page
- General lobby
- Game lobby (preparation)
- Game in progress

## Functionality
This paragraph describes the functionality of the application.

### Login
The first step that the user encounters is the login page. If the user has already an account, the user can use their username/email and password to login to the game. If the user does not have an account, they have the option to register.

### Register
If the user does not have an account, they can register giving a username, email and password.

### Lobby
When the user logs in, he/she can see a page with all the games with the status **created** which means that the game is created and available for players to join. The user can select a game to open a game preparation page, or create a new game to get in that page too.

### Game preparation
When the user gets in this step, he/she can select a team to join. After he/she joins the team, he/she can change the name of the team, or move to the other team. After the teams are ready, (we have to think about that)
- The creator can start the game
- The creator can lock the game and everybody have to accept the game to start

### The game
When the game starts the game status becomes **in progress**, 25 random words are selected to create the 5x5 table, and a random map is created. The first 2 players from each team are selected as the codemasters and they can now see the map that was generated.

The map indicates which team starts with the signs around it, so the codemaster of the starting team can give the first clue (a word and a number).

When the codemaster gives the clue, his/her teammates can guess a word each time. His/her teammates can guess cards. They have to guess at least one card. If the player has found a card that belongs to his/her team, he/she can guess the next one, until, he/she finds a neutral card, an opponent card, or he/she wants to stop guessing. If the player finds the murderer, the round finishes immidiately.

When the first turn finishes, the same goes for the next codemaster and the next team.

When all cards of one team are found, the team wins and the other one loses. A team can also lose if they find the murderer as mentioned before.

  

## Technically
This section covers the technical details of how the application works. All the requests are documented at **codenames-back/documentation/swagger.yaml** and exported into html at codenames-back/documentation/swagger.html (exported with redoc-cli).

### Register
A user makes a request to register with email, username and password to register.

### Login
A user makes a request to login with the username/email and password. This starts a session for the user, so the frontend application does not have to send a token for authentication. The user's identity is checked using a session cookie.

### Lobby
The user makes a request to take the available games.

### New game
The user makes a request to create a new game. This creates 2 new teams. The user selects the team to join by sending the team id.

### Join a game
A user sends a request to join a team on a game providing the team id.  When a user joins a team, a new socket is opened to listen to a certain subject with the id game\_(id)\_updates and another one game\_(id)\_messages.

For example: game\_18\_updates and game\_18\_messages.

The one subject is used to get updates about the game progress, and the other in order to exchange messages respectively.

### Start Game

When the teams are ready, the game creator sends a request with the game id to lock the game, in order to prevent other users from joining the game. When this is done, a message is sent to all the users through the updates socket, in order to inform them that the game is ready to start, and they have to make a request to confirm. When everybody confirms, the game starts.

### Game progress
When the game starts, the game's status becomes **in_progress** all players get a message through the updates subject that the game is started. The round's and the turn's status becomes also **in_progress**. After that all the players have to make a request and get the game object which includes the words, the first round and the first turn within it. The turn informs everybody with the information who is the codemaster who starts.

The first codemaster gives the first clue by doing a request with the word and the number. After this request everybody is informed through the updates subject.

Now it is the turn of the teammates to guess. This is done with a new request from the teammates. Every time a new word is selected through the request, a new message is sent through the updates subject, in order to inform them which word is chosen. When the teammates finish guessing, they have to make another request to finish their turn (the turn status becomes **finished**. Again, everybody is informed through the updates subject. Everybody make a request to get the round status (the round includes the turns with a new turn added and the turn includes the information about the next codemaster).

The next codemaster starts over, and the game continues as before.

If while the teammates guess words, there is a chance that they find the last word of a team, or the hacker. In this case, the turn and the round get the status finished and everybody is informed through the updates subject. If they want one more round, everybody has to make a request to continue. 

If someone does not want, he has to make a request to decline one more round, and the game gets the status finished. Everybody can make a request to get the game object to see the stats and the score of the game.
