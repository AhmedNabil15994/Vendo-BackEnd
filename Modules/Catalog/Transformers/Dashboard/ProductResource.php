<?php

namespace Modules\Catalog\Transformers\Dashboard;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'title' => $this->title,
            'image' => $this->image ? url($this->image) : null,
            'status' => ajaxSwitch($this, url(route('dashboard.products.switch', [$this->id, 'status']))),
            'print_status' => $this->status ? __('apps::dashboard.datatable.active') :  __('apps::dashboard.datatable.unactive'),
            'price' => $this->price,
            'qty' => $this->qty,
            'vendor' => $this->vendor->title,
            'deleted_at' => $this->deleted_at,
            'created_at' => date('d-m-Y', strtotime($this->created_at)),
        ];

        if ($this->categories) {
            $numItems = count($this->categories->toArray());
            $i = 0;
            $title = '';
            foreach ($this->categories as $k => $role) {
                $title .= $role->title;
                if (++$i !== $numItems) { // if it is not the last index
                    $title .= ' - ';
                }
            }
            $data['categories'] = $title;
        } else {
            $data['categories'] = '';
        }

        return $data;
    }
}
