<?php

namespace App\Repositories\Client;

use App\Models\Client\Client;
use App\Models\Client\Contact;
use App\Repositories\BaseRepository;
use Illuminate\Support\Arr;

class ClientRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Client::class;
    }

    public function getClientByUserId(int $id)
    {
        return $this->model
            ->where('user_id', $id)
            ->first();
    }

    public function createWithContacts(array $attributes): Client
    {
        $contacts = Arr::pull($attributes, 'contacts', []);
        /** @var Client $client */
        $client = parent::create($attributes);
        if (!empty($contacts)) {
            $client->contacts()->createMany($contacts);
        }
        return $client;
    }

    public function updateWithContacts(int $clientId, array $attributes): Client
    {
        $contacts = Arr::pull($attributes, 'contacts', null);
        /** @var Client $client */
        $client = parent::update($attributes, $clientId);

        if (is_array($contacts)) {
            $existingIds = $client->contacts()->pluck('id')->toArray();
            $incomingIds = [];

            foreach ($contacts as $contactData) {
                if (!empty($contactData['id']) && in_array($contactData['id'], $existingIds)) {
                    $contactId = (int) $contactData['id'];
                    $incomingIds[] = $contactId;
                    // Do not allow changing client_id via mass assign
                    unset($contactData['client_id']);
                    $client->contacts()->where('id', $contactId)->update($contactData);
                } else {
                    $client->contacts()->create($contactData);
                }
            }

            // Delete contacts that were not included in the update payload
            $toDelete = array_diff($existingIds, $incomingIds);
            if (!empty($toDelete)) {
                $client->contacts()->whereIn('id', $toDelete)->delete();
            }
        }

        return $client;
    }

    public function updateProfile(int $userId, array $attributes): Client
    {
        return \DB::transaction(function () use ($userId, $attributes) {
            $client = $this->getClientByUserId($userId);
            
            if (!$client) {
                throw new \Exception('No se encontró el perfil del cliente');
            }

            // Separar datos del cliente y del usuario
            $clientData = [];
            $userData = [];

            // Campos que van a la tabla clients
            $clientFields = ['name', 'address', 'city', 'phone'];
            foreach ($clientFields as $field) {
                if (isset($attributes[$field])) {
                    $clientData[$field] = $attributes[$field];
                }
            }

            // Si se proporciona document, actualizar identifier
            if (isset($attributes['document'])) {
                $clientData['identifier'] = $attributes['document'];
            }

            // Campos que van a la tabla users
            if (isset($attributes['email'])) {
                $userData['email'] = $attributes['email'];
            }

            // Actualizar datos del usuario si hay cambios
            if (!empty($userData)) {
                $userRepo = app(\App\Repositories\User\UserRepository::class);
                $userRepo->updateProfile($userId, $userData);
            }

            // Actualizar datos del cliente
            if (!empty($clientData)) {
                return $this->update($clientData, $client->id);
            }

            return $client;
        });
    }
}
