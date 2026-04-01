<?php

class InvoiceRepository {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Get all invoices for a company
     */
    public function getAllByCompany($companyId) {
        $stmt = $this->pdo->prepare("SELECT * FROM cp_invoices WHERE company_id = ? ORDER BY id DESC");
        $stmt->execute([$companyId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get invoice by ID
     */
    public function getById($id, $companyId = null) {
        $sql = "SELECT * FROM cp_invoices WHERE id = ?";
        $params = [$id];
        if ($companyId) {
            $sql .= " AND company_id = ?";
            $params[] = $companyId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Update invoice status
     */
    public function updateStatus($id, $status, $paymentData = []) {
        $sql = "UPDATE cp_invoices SET status = ?";
        $params = [$status];

        if ($status === 'paid') {
            $sql .= ", paid_at = NOW()";
            if (isset($paymentData['gateway_id'])) {
                $sql .= ", gateway_id = ?";
                $params[] = $paymentData['gateway_id'];
            }
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Create a new invoice
     */
    public function create($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO cp_invoices (company_id, amount, due_date, status, type, description) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['company_id'],
            $data['amount'],
            $data['due_date'],
            $data['status'] ?? 'pending',
            $data['type'] ?? 'recurring',
            $data['description'] ?? null
        ]);
    }
}
