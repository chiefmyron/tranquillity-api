<?php namespace Tranquility\Resources;

class UserResource extends AbstractResourceItem {
    /**
     * Transform the resource into an array.
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @return array
     */
    public function toArray($request) {
        return parent::toArray($request);
    }

    public function getAttributes($request) {
        // Get common entity attributes
        $entity = parent::getAttributes($request);

        // Map entity data to attributes
        $attributes = [
            'username' => $this->data->username,
            'timezoneCode' => $this->data->timezoneCode,
            'localeCode' => $this->data->localeCode,
            'active' => $this->data->active,
            'securityGroupId' => $this->data->securityGroupId,
            'registeredDateTime' => $this->data->registeredDateTime
        ];
        $attributes = array_merge($entity, $attributes);

        // If a sparse fieldset has been specified, apply it before returning
        $attributes = $this->_applySparseFieldset($request, $attributes);
        return $attributes;
    }

    public function getRelationships($request) {
        $relationships = [
            'updatedByUser' => [
                'links' => [
                    'self' => $this->generateUri($request, 'users-relationship', ['id' => $this->data->id, 'resource' => 'updatedByUser']),
                    'related' => $this->generateUri($request, 'users-related', ['id' => $this->data->id, 'resource' => 'updatedByUser'])
                ],
                'data' => [
                    'type' => $this->data->audit->user->type,
                    'id' => $this->data->audit->user->id
                ]
            ]
        ];

        // If a sparse fieldset has been specified, apply it before returning
        $relationships = $this->_applySparseFieldset($request, $relationships);
        return $relationships;
    }

    public function getLinks($request) {
        $links = [
            'self' => $this->generateUri($request, 'users-detail', ['id' => $this->data->id])
        ];
        return $links;
    }
}