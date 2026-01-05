<?php
// sdg_module.php
require_once 'config/database.php';

class SDGModule {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function getAllSDGs() {
        $stmt = $this->pdo->query("SELECT * FROM sdg_contents WHERE is_active = 1 ORDER BY sdg_number");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getSDGByNumber($number) {
        $stmt = $this->pdo->prepare("SELECT * FROM sdg_contents WHERE sdg_number = ? AND is_active = 1");
        $stmt->execute([$number]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getFeaturedSDGs($limit = 6) {
        $stmt = $this->pdo->prepare("SELECT * FROM sdg_contents WHERE is_active = 1 ORDER BY sdg_number LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>