<?php

namespace MyApp;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Chat implements MessageComponentInterface
{
    protected $clients;
    protected $positions;

    public function __construct()
    {
        // Tüm bağlantılar burada tutulacak.
        $this->clients = new \SplObjectStorage;

        // Tüm oyuncuların pozisyonlarının tutulacağı bir array.
        $this->positions = array();

        // Sunucu başlatıldı mesajı konsola gönder.
        echo "Sunucu Başlatıldı.";
    }

    public function onOpen(ConnectionInterface $conn)
    {
        // Yeni bağlantıyı clients'de sakla.
        $this->clients->attach($conn);
        // Yeni bağlantısının pozisyonunu 0, 0 olarak ayarla.
        $this->positions[$conn->resourceId] = ['x' => 5, 'y' => 5];

        $resourceId = $conn->resourceId;
        // Yeni katılan clientin id'sini tüm clientlere gönder. 
        foreach ($this->clients as $client)
        {
            $client->send(json_encode(['type' => 'connect', 'resourceId' => $resourceId]));
        }

        $this->broadcastPlayerList();
        // Konsola yeni bağlantı adında mesaj gönder.
        echo "Yeni bağlantı! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        // JSON formatında gelen mesajı decode et.
        $data = json_decode($msg, true);

        if ($data && isset($data['type']) && $data['type'] === 'move') {
            
            // ResourceID yoluyla mesajı gönderen clientin şuanki pozisyonunu al.
            $currentPosition = $this->positions[$from->resourceId];

            // newPosition adında bir array oluştur ve bu array içinde yeni x ve y konumlarını belirle.
            $newPosition = [
                // Şuanki x pozisyonu ile mesajda gelen x pozisyonunu topla.
                'x' => $currentPosition['x'] + $data['position']['x'],
                // Şuanki y pozisyonu ile mesajda gelen y pozisyonunu topla.
                'y' => $currentPosition['y'] + $data['position']['y']
            ];

            // Mesajı gönderen clientin pozisyonunu değiştir.
            $this->positions[$from->resourceId] = $newPosition;

            // Tüm client'lere oyuncuların pozisyonlarını gönder. 
            foreach ($this->clients as $client) {
                $client->send(json_encode(['type' => 'update', 'positions' => $this->positions]));
            }

            //echo "Position updated: " . json_encode($this->positions[$from->resourceId]) . "\n";
        }
    }


    public function onClose(ConnectionInterface $conn)
    {
        // Çıkan clientin id'sini al.
        $resourceId = $conn->resourceId;

        // Tüm clientlere hangi clientin çıktığını gönder.
        foreach ($this->clients as $client) {
            $client->send(json_encode(['type' => 'disconnect', 'resourceId' => $resourceId]));
        }
        // Client sunucudan çıktığında clients'den o bağlantıyı sil.
        $this->clients->detach($conn);

        // Sunucudan çıkan clientin pozisyonunuda sil (İleride veritabanında kullanıcının son olarak kaldığı pozisyonu saklayabiliriz.)
        unset($this->positions[$conn->resourceId]); 

        // Konsola hangi clientin çıktığını yazdır.
        echo "Client {$conn->resourceId} ayrıldı.\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "Bir hata oluştu: {$e->getMessage()}\n";

        $conn->close();
    }
    protected function broadcastPlayerList() {
        $playerList = array();
    
        foreach ($this->clients as $client) {
            $resourceId = $client->resourceId;
    
            // Örnek olarak, bağlantının mevcut pozisyonunu al
            $position = isset($this->positions[$resourceId]) ? $this->positions[$resourceId] : ['x' => 5, 'y' => 5];
    
            $playerList[$resourceId] = [
                'position' => $position, 
            ];
        }
    
        foreach ($this->clients as $client) {
            $client->send(json_encode(['type' => 'playerList', 'players' => $playerList]));
        }
    }
}
