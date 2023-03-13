<?php

namespace apiOrganigrama\Models;

use Illuminate\Database\Eloquent\Model;

class AreasUser extends Model
{
  public $table = 'users_areas';
  public $timestamps = false;
  protected $primaryKey = 'id_user';

}
