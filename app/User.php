<?php

namespace App;

use CKSource\CKFinder\Image;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;


class User extends Authenticatable
{
    use Notifiable;

    const IS_BANNED = 1;
    const IS_ACTIVE = 0;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'description'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public static function add($fields)
    {
        $user = new static;
        $user->fill($fields);
        $user->save();

        return $user;
    }

    public function edit($fields)
    {
        $this->fill($fields);
        $this->save();
    }

    public function generatePassword($password){
        if($password != null) {
            $this->password = bcrypt($password);
            $this->save();
        }
    }

    public function remove()
    {
        $this->removeAvatar();
        $this->delete();
    }

    public function uploadAvatar($image)
    {
        if($image == null) return;

        $this->removeAvatar();
        $filename = str_random(10) . '.' . $image->extension();
        $manager = new ImageManager();

        //resize image with Intervention Image
        $resImage = $manager->make($image)->resize(109, null, function ($constraint) {
            $constraint->aspectRatio(); // auto height
        });
        $resImage->save("uploads/$filename");

//      $image->storeAs('uploads', $filename, $resImage); //
        $this->avatar = $filename;
        $this->save();
    }

    public function removeAvatar()
    {
        if($this->avatar != null){
            Storage::delete('uploads/' . $this->avatar);
        }
    }

    public function getImage()
    {
        if($this->avatar == null)
        {
            return '/img/no-user-image.png';
        }

        return '/uploads/' . $this->avatar;
    }

    public function makeAdmin()
    {
        $this->is_admin = 1;
        $this->save();
    }

    public function makeNormal()
    {
        $this->is_admin = 0;
        $this->save();
    }

    public function toggleAdmin($value)
    {
        if($value == null)
        {
            return $this->makeNormal();
        }

        return $this->makeAdmin();
    }

    public function ban()
    {
        $this->status = User::IS_BANNED;
        $this->save();
    }
    public function unban()
    {
        $this->status = User::IS_ACTIVE;
        $this->save();
    }

    public function toggleBan($value)
    {
        if($value == null)
        {
            return $this->ban();
        }

        return $this->unban();
    }
}
