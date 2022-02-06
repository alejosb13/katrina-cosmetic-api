<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'categoria_id',
        'frecuencia_id',
        'nombre',
        'celular',
        'telefono',
        'direccion_casa',
        'direccion_negocio',
        'cedula',
        'dias_cobro',
        'fecha_vencimiento',
        'estado',
    ];
    
    // one to many
    public function facturas()
    {
        return $this->hasMany(Factura::class);
    }

    // one to many inversa
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    // one to many inversa
    public function frecuencia()
    {
        return $this->belongsTo(Frecuencia::class,"frecuencia_id","id");
    }
}
