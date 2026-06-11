<?php

declare(strict_types=1);

final class LocationRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getAllStates(): array
    {
        $sql = 'SELECT state_id, state_name FROM states ORDER BY state_name ASC';
        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }

    public function getCitiesByState(int $stateId): array
    {
        $sql = 'SELECT city_id, city_name
                FROM cities
                WHERE state_id = :state_id
                ORDER BY city_name ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':state_id', $stateId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getAreasByCity(int $cityId): array
    {
        $sql = 'SELECT area_id, area_name, pincode
                FROM areas
                WHERE city_id = :city_id
                ORDER BY area_name ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':city_id', $cityId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getLocationByPincode(string $pincode): array
    {
        $sql = 'SELECT s.state_id, s.state_name, c.city_id, c.city_name, a.area_id, a.area_name, a.pincode
                FROM areas a
                INNER JOIN cities c ON c.city_id = a.city_id
                INNER JOIN states s ON s.state_id = c.state_id
                WHERE a.pincode = :pincode
                ORDER BY a.area_name ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':pincode', $pincode, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getStateById(int $stateId): ?array
    {
        $sql = 'SELECT state_id, state_name
                FROM states
                WHERE state_id = :state_id
                LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':state_id', $stateId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    public function getCityById(int $cityId): ?array
    {
        $sql = 'SELECT c.city_id, c.city_name, s.state_id, s.state_name
                FROM cities c
                INNER JOIN states s ON s.state_id = c.state_id
                WHERE c.city_id = :city_id
                LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':city_id', $cityId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    public function getAreaById(int $areaId): ?array
    {
        $sql = 'SELECT s.state_id, s.state_name, c.city_id, c.city_name, a.area_id, a.area_name, a.pincode
                FROM areas a
                INNER JOIN cities c ON c.city_id = a.city_id
                INNER JOIN states s ON s.state_id = c.state_id
                WHERE a.area_id = :area_id
                LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':area_id', $areaId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }
}
