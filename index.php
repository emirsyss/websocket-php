<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebSockets Game</title>
    <style>
        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
        }
        #game-container {
            width: 100%;
            min-height: 100vh;

            background-color: aliceblue;
        }

        .player {
            border-radius: 1px;
            position: absolute;
            background-color: dodgerblue;
            width: 30px;
            height: 15px;
            transition: left 0.4s ease, top 0.4s ease;
            color: white;
        }
    </style>
</head>

<body>
    <div id="game-container"></div>

    <script src="Game/game.js"></script>
</body>

</html>