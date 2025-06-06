<?php

namespace Modules\Catalog\Repositories\Vendor;

use Illuminate\Support\Facades\DB;
use Modules\Catalog\Entities\AddonCategory;
use Modules\Core\Traits\CoreTrait;

class AddonCategoryRepository
{
    use CoreTrait;

    protected $category;

    public function __construct(AddonCategory $category)
    {
        $this->category = $category;
    }

    public function getAll($order = 'sort', $sort = 'asc')
    {
        return $this->category->whereHas('vendor.sellers', function ($query) {
            $query->where('seller_id', auth()->user()->id);
        })->orderBy($order, $sort)->get();
    }

    public function getAllActive($order = 'sort', $sort = 'asc', $vendorId = null)
    {
        $query = $this->category->query();
        if (!is_null($vendorId)) {
            $query->where('vendor_id', $vendorId);
        } else {
            $query = $query->whereHas('vendor.sellers', function ($query) {
                $query->where('seller_id', auth()->user()->id);
            });
        }
        return $query->orderBy($order, $sort)->get();
    }

    public function findById($id)
    {
        $category = $this->category->whereHas('vendor.sellers', function ($query) {
            $query->where('seller_id', auth()->user()->id);
        })->withDeleted()->find($id);
        return $category;
    }

    public function create($request)
    {
        DB::beginTransaction();

        try {
            $data = [
                'sort' => $request->sort ?? 0,
                "title" => $request->title,
                "vendor_id" => $request->vendor_id,
            ];

            /*if (!is_null($request->image)) {
            $imgName = $this->uploadImage(public_path(config('core.config.addon_img_path')), $request->image);
            $data['image'] = config('core.config.addon_img_path') . '/' . $imgName;
            }*/

            $category = $this->category->create($data);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function update($request, $id)
    {
        DB::beginTransaction();

        $category = $this->findById($id);
        $restore = $request->restore ? $this->restoreSoftDelete($category) : null;

        try {
            $data = [
                'sort' => $request->sort ?? 0,
                "title" => $request->title,
                "vendor_id" => $request->vendor_id,
            ];

            /*if ($request->image) {
            if (!empty($category->image) && !in_array($category->image, config('core.config.special_images'))) {
            File::delete($category->image); ### Delete old image
            }
            $imgName = $this->uploadImage(public_path(config('core.config.addon_img_path')), $request->image);
            $data['image'] = config('core.config.addon_img_path') . '/' . $imgName;
            } else {
            $data['image'] = $category->image ?? null;
            }*/

            $category->update($data);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function restoreSoftDelete($model)
    {
        $model->restore();
        return true;
    }

    public function delete($id)
    {
        DB::beginTransaction();

        try {
            $model = $this->findById($id);
            /*if ($model && !empty($model->image) && !in_array($model->image, config('core.config.special_images'))) {
            File::delete($model->image); ### Delete old image
            }*/

            if ($model->trashed()):
                $model->forceDelete();
            else:
                $model->delete();
            endif;

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function deleteSelected($request)
    {
        DB::beginTransaction();

        try {
            foreach ($request['ids'] as $id) {
                $model = $this->delete($id);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function QueryTable($request)
    {
        $query = $this->category
            ->where(function ($query) use ($request) {
                $query->where('id', 'like', '%' . $request->input('search.value') . '%');
                $query->orWhere(function ($query) use ($request) {
                    $query->where('title', 'like', '%' . $request->input('search.value') . '%');
                    $query->orWhere('slug', 'like', '%' . $request->input('search.value') . '%');
                });
            })
            ->whereHas('vendor.sellers', function ($query) {
                $query->where('seller_id', auth()->user()->id);
            });

        return $this->filterDataTable($query, $request);
    }

    public function filterDataTable($query, $request)
    {
        // Search Categories by Created Dates
        if (isset($request['req']['from']) && $request['req']['from'] != '') {
            $query->whereDate('created_at', '>=', $request['req']['from']);
        }

        if (isset($request['req']['to']) && $request['req']['to'] != '') {
            $query->whereDate('created_at', '<=', $request['req']['to']);
        }

        if (isset($request['req']['deleted']) && $request['req']['deleted'] == 'only') {
            $query->onlyDeleted();
        }

        if (isset($request['req']['deleted']) && $request['req']['deleted'] == 'with') {
            $query->withDeleted();
        }

        if (isset($request['req']['vendor_id']) && !empty($request['req']['vendor_id'])) {
            $query->where('vendor_id', $request['req']['vendor_id']);
        }

        return $query;
    }
}
