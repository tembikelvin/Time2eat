<?php

namespace controllers;

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/UserAddress.php';

use core\BaseController;
use Time2Eat\Models\UserAddress;

/**
 * Address Controller
 * Handles user address management
 */
class AddressController extends BaseController
{
    private UserAddress $addressModel;

    public function __construct()
    {
        parent::__construct();
        $this->addressModel = new UserAddress();
    }

    /**
     * Get user addresses
     */
    public function index(): void
    {
        $this->requireAuth();
        
        $userId = $this->getCurrentUser()->id;
        $addresses = $this->addressModel->getByUser($userId);
        
        $this->jsonResponse([
            'success' => true,
            'addresses' => $addresses
        ]);
    }

    /**
     * Get address by ID
     */
    public function show(int $id): void
    {
        $this->requireAuth();
        
        $userId = $this->getCurrentUser()->id;
        $address = $this->addressModel->getById($id, $userId);
        
        if (!$address) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Address not found'
            ], 404);
            return;
        }
        
        $this->jsonResponse([
            'success' => true,
            'address' => $address
        ]);
    }

    /**
     * Create new address
     */
    public function store(): void
    {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $input = $this->getJsonInput();
        $userId = $this->getCurrentUser()->id;

        // Prepare address data
        $addressData = [
            'user_id' => $userId,
            'label' => $input['label'] ?? '',
            'address_line_1' => $input['address_line_1'] ?? '',
            'address_line_2' => $input['address_line_2'] ?? null,
            'city' => $input['city'] ?? '',
            'state' => $input['state'] ?? null,
            'postal_code' => $input['postal_code'] ?? null,
            'country' => $input['country'] ?? 'Cameroon',
            'latitude' => $input['latitude'] ?? null,
            'longitude' => $input['longitude'] ?? null,
            'is_default' => $input['is_default'] ?? false,
            'delivery_instructions' => $input['delivery_instructions'] ?? null
        ];

        // Validate address data
        $errors = $this->addressModel->validateAddressData($addressData);
        if (!empty($errors)) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $errors
            ], 400);
            return;
        }

        // Create address
        $result = $this->addressModel->createAddress($addressData);
        
        if ($result['success']) {
            $this->jsonResponse($result, 201);
        } else {
            $this->jsonResponse($result, 400);
        }
    }

    /**
     * Update address
     */
    public function update(int $id): void
    {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'PATCH') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $input = $this->getJsonInput();
        $userId = $this->getCurrentUser()->id;

        // Check if address exists
        $address = $this->addressModel->getById($id, $userId);
        if (!$address) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Address not found'
            ], 404);
            return;
        }

        // Prepare update data
        $updateData = [];
        $allowedFields = ['label', 'address_line_1', 'address_line_2', 'city', 'state', 'postal_code', 'country', 'latitude', 'longitude', 'is_default', 'delivery_instructions'];
        
        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $updateData[$field] = $input[$field];
            }
        }

        if (empty($updateData)) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'No data to update'
            ], 400);
            return;
        }

        // Validate address data
        $errors = $this->addressModel->validateAddressData(array_merge($address, $updateData));
        if (!empty($errors)) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $errors
            ], 400);
            return;
        }

        // Update address
        $result = $this->addressModel->updateAddress($id, $userId, $updateData);
        $this->jsonResponse($result, $result['success'] ? 200 : 400);
    }

    /**
     * Delete address
     */
    public function destroy(int $id): void
    {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $userId = $this->getCurrentUser()->id;

        // Check if address exists
        $address = $this->addressModel->getById($id, $userId);
        if (!$address) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Address not found'
            ], 404);
            return;
        }

        // Delete address
        $result = $this->addressModel->deleteAddress($id, $userId);
        $this->jsonResponse($result, $result['success'] ? 200 : 400);
    }

    /**
     * Set address as default
     */
    public function setDefault(int $id): void
    {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        $userId = $this->getCurrentUser()->id;

        // Check if address exists
        $address = $this->addressModel->getById($id, $userId);
        if (!$address) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Address not found'
            ], 404);
            return;
        }

        // Set as default
        $result = $this->addressModel->setAsDefault($id, $userId);
        $this->jsonResponse($result, $result['success'] ? 200 : 400);
    }

    /**
     * Get default address
     */
    public function getDefault(): void
    {
        $this->requireAuth();
        
        $userId = $this->getCurrentUser()->id;
        $address = $this->addressModel->getDefaultAddress($userId);
        
        $this->jsonResponse([
            'success' => true,
            'address' => $address
        ]);
    }

    /**
     * Search addresses by location
     */
    public function searchByLocation(): void
    {
        $this->requireAuth();
        
        $input = $this->getJsonInput();
        $userId = $this->getCurrentUser()->id;
        
        $latitude = $input['latitude'] ?? null;
        $longitude = $input['longitude'] ?? null;
        $radius = $input['radius'] ?? 5; // Default 5km radius
        
        if (!$latitude || !$longitude) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Latitude and longitude are required'
            ], 400);
            return;
        }
        
        $addresses = $this->addressModel->searchByLocation($userId, $latitude, $longitude, $radius);
        
        $this->jsonResponse([
            'success' => true,
            'addresses' => $addresses
        ]);
    }
}
