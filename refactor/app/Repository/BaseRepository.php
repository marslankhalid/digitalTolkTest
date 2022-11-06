<?php

namespace DTApi\Repository;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Validator;
use Illuminate\Database\Eloquent\Model;
use DTApi\Exceptions\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BaseRepository
{

    /**
     * @var Model
     */
    protected Model $model;

    /**
     * @var array
     */
    protected array $validationRules = [];

    /**
     * @param Model|null $model
     */
    public function __construct(Model $model = null)
    {
        $this->model = $model;
    }

    /**
     * @return array
     */
    public function validatorAttributeNames(): array
    {
        return [];
    }

    /**
     * @return Model|null
     */
    public function getModel(): ?Model
    {
        return $this->model;
    }

    /**
     * @return Collection|Model[]
     */
    public function all(): array|Collection
    {
        return $this->model->all();
    }

    /**
     * @param integer $id
     * @return Model|null
     */
    public function find(int $id): ?Model
    {
        return $this->model->find($id);
    }

    public function with($array): Builder
    {
        return $this->model->with($array);
    }

    /**
     * @param integer $id
     * @return Model
     * @throws ModelNotFoundException
     */
    public function findOrFail(int $id): Model
    {
        return $this->model->findOrFail($id);
    }

    /**
     * @param string $slug
     * @return Model
     * @throws ModelNotFoundException
     */
    public function findBySlug(string $slug): Model
    {
        return $this->model->where('slug', $slug)->first();
    }

    /**
     * @return Builder
     */
    public function query(): Builder
    {
        return $this->model->query();
    }

    /**
     * @param array $attributes
     * @return Model
     */
    public function instance(array $attributes = []): Model
    {
        $model = $this->model;
        return new $model($attributes);
    }

    /**
     * @param int|null $perPage
     * @return mixed
     */
    public function paginate(int $perPage = null): mixed
    {
        return $this->model->paginate($perPage);
    }

    /**
     * @param $key
     * @param $where
     * @return mixed
     */
    public function where($key, $where): mixed
    {
        return $this->model->where($key, $where);
    }

    /**
     * @param array $data
     * @param null $rules
     * @param array $messages
     * @param array $customAttributes
     * @return \Illuminate\Validation\Validator
     */
    public function validator(array $data = [], $rules = null, array $messages = [], array $customAttributes = []): \Illuminate\Validation\Validator
    {
        if (is_null($rules)) {
            $rules = $this->validationRules;
        }

        return Validator::make($data, $rules, $messages, $customAttributes);
    }

    /**
     * @param array $data
     * @param null $rules
     * @param array $messages
     * @param array $customAttributes
     * @return bool
     * @throws ValidationException
     */
    public function validate(array $data = [], $rules = null, array $messages = [], array $customAttributes = []): bool
    {
        $validator = $this->validator($data, $rules, $messages, $customAttributes);
        return $this->_validate($validator);
    }

    /**
     * @param array $data
     * @return Model
     */
    public function create(array $data = []): Model
    {
        return $this->model->create($data);
    }

    /**
     * @param integer $id
     * @param array $data
     * @return Model
     */
    public function update(int $id, array $data = []): Model
    {
        $instance = $this->findOrFail($id);
        $instance->update($data);
        return $instance;
    }

    /**
     * @param integer $id
     * @return Model
     * @throws \Exception
     */
    public function delete(int $id): Model
    {
        $model = $this->findOrFail($id);
        $model->delete();
        return $model;
    }

    /**
     * @param \Illuminate\Validation\Validator $validator
     * @return bool
     * @throws ValidationException
     */
    protected function _validate(\Illuminate\Validation\Validator $validator): bool
    {
        if (!empty($attributeNames = $this->validatorAttributeNames())) {
            $validator->setAttributeNames($attributeNames);
        }

        if ($validator->fails()) {
            return false;
            throw (new ValidationException)->setValidator($validator);
        }

        return true;
    }

}
