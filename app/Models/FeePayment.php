<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class FeePayment extends Model
{
    use BelongsToInstitution;
    use BelongsToBranch;
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'decimal:2',
    ];
 
    /* ---------- Relations ---------- */
 
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
 
    public function invoice()
    {
        return $this->belongsTo(FeeInvoice::class, 'fee_invoice_id');
    }
 
    public function officeAccount()
    {
        return $this->belongsTo(OfficeAccount::class);
    }

}