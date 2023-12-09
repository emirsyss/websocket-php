var conn = new WebSocket('ws://localhost:8080');
const gamecontainer = document.getElementById('game-container');
let myResourceId;

conn.onopen = function (e) {
    console.log("Bağlantı Başarılı!");
};

conn.onmessage = function (e) {
    const data = JSON.parse(e.data);

    if (data.type === 'update') {
        // Sunucudan alınan verilere göre konumları güncelle.
        updatePositions(data.positions);
    }
    if (data.type === 'disconnect') {
        const disconnectedResourceId = data.resourceId;

        removePlayerElement(disconnectedResourceId);
    }
    if (data.type === 'connect') {
        const connectedResourceId = data.resourceId;
        myResourceId = connectedResourceId;
        createPlayerElement(connectedResourceId);
    }
    if (data.type === 'playerList') {
        // Sunucudaki diğer oyuncuları ekranda göster.
        updatePlayerList(data.players);
    }
};

document.addEventListener('keydown', function (event) {
    const keyCode = event.keyCode;
    let position = {
        x: 0,
        y: 0
    };

    switch (keyCode) {
        case 37: // Sol yön tuşu
            position.x -= 5;
            break;
        case 38: // Yukarı yön tuşu
            position.y -= 5;
            break;
        case 39: // Sağ yön tuşu
            position.x += 5;
            break;
        case 40: // Aşağı yön tuşu
            position.y += 5;
            break;
    }

    // Hareket verilerini sunucuya gönder.
    conn.send(JSON.stringify({
        type: 'move',
        position: position
    }));

});

function updatePlayerList(players) {
    // Mevcut oyuncu listesini güncelle (eğer gerekliyse).
    // Örneğin, yeni bağlanan oyuncuları game container'a ekleyebiliriz.
    for (const resourceId in players) {
        const playerInfo = players[resourceId];

        // Her bir bağlantı için pozisyon bilgilerini al.
        const position = playerInfo.position;

        // Bağlantının HTML elementini al.
        let player = document.getElementById(resourceId);

        // Eğer bağlantıya ait element yoksa oluştur.
        if (!player) {
            player = document.createElement('div');
            player.id = resourceId;
            player.className = 'player';
            gamecontainer.appendChild(player);
            player.innerHTML += resourceId;
        }

        // Hedef pozisyonlarını hesapla.
        const targetPositionX = position.x * 10;
        const targetPositionY = position.y * 10;

        // Oyuncu elementinin stilini güncelle.
        player.style.left = targetPositionX + 'px';
        player.style.top = targetPositionY + 'px';
    }
}
function createPlayerElement(resourceId) {
    // Bağlantının HTML elementini al.
    let player = document.getElementById(resourceId);

    // Eğer bağlantıya ait element yoksa oluştur.
    if (!player) {
        player = document.createElement('div');
        player.id = resourceId;
        player.className = 'player';
        gamecontainer.appendChild(player);
        player.innerHTML += resourceId;

        // Başlangıç pozisyonunu belirle (örneğin, rastgele bir pozisyon).
        const initialPositionX = 5 * 10;
        const initialPositionY = 5 * 10;

        // Oyuncu elementinin stilini güncelle.
        player.style.left = initialPositionX + 'px';
        player.style.top = initialPositionY + 'px';
    }
}


function removePlayerElement(resourceId) {
    const playerToRemove = document.getElementById(resourceId);

    // Bağlantıya ait element varsa kaldır.
    if (playerToRemove) {
        playerToRemove.remove();
    }
}

function updatePositions(positions) {


    for (const resourceId in positions) {
        // Her bir bağlantı için pozisyon bilgilerini al.
        const position = positions[resourceId];

        // Bağlantının HTML elementini al.
        let player = document.getElementById(resourceId);

        // Eğer bağlantıya ait element yoksa oluştur.
        if (!player) {
            createPlayerElement(resourceId);
            player = document.getElementById(resourceId);
        }

        // Hedef pozisyonlarını hesapla.
        const targetPositionX = position.x * 10;
        const targetPositionY = position.y * 10;

        // Oyuncu elementinin stilini güncelle.
        player.style.left = targetPositionX + 'px';
        player.style.top = targetPositionY + 'px';

    }
}