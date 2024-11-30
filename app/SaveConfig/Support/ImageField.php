<?php

namespace App\SaveConfig\Support;
use illuminate\Database\Eloquent\Model;
use illuminate\Htpp\UploadFile;
use illuminate\Support\Facades\Storage;

use Closure;
class ImageField extends Field
{
    private string $folder ='images';
    private ?string $disk  =null;
    private ?Closure $fileNameClosure = null;
    private  $deleteImageOnUpdate= true;

    public function execute()
    {
        if(!$this->value)
        {
            return $this->value;
        }
        
        if(!$this->value instanceof UploadedFile)
        {
            return $this->value;
        }
        if($this->isUpdate() && $this->deleteImageOnUpdate)
        {
            Storage::delete($this->model->getRawOriginal($this->column));
        }
        if(!$this->fileNameClosure)
        {
            return $this->value->store($this->folder, $this->diskName);
        }

        $fileName = ($this->fileNameClosure)($this->value);

        return $this->value->storeAs($this->folder, $fileName, $this->diskName);
    }

    public function StoreToFolder(string $folder)
    {
        $this->folder = $folder;
        return $this;
    }

    public function disk(string $disk)
    {
       $this->disk = $disk;
       return $disk;
    }

    public function diskName():string
    {
        return $this->disk ?? config('filesystems.default');
    }

    public function fileName(Closure $closure)
    {
        $this->fileNameClosure =$closure;
        return $this;   
    }

    public function donDeletePreviousImage()
    {
        $this->deleteImageOnUpdate=false;
        return $this;
    }
}