<div>
    <h4 class="mb-3" data-en="Submit a Grievance" data-bn="অভিযোগ দাখিল করুন">Submit a Grievance</h4>

    <div class="card p-3" style="max-width: 700px;">
        <div class="mb-3">
            <label class="form-label" data-en="Category" data-bn="বিভাগ">Category</label>
            <input type="text" wire:model="category" class="form-control" placeholder="e.g. Teaching Quality, Facilities, Harassment...">
            @error('category') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" data-en="Subject" data-bn="বিষয়">Subject</label>
            <input type="text" wire:model="subject" class="form-control">
            @error('subject') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" data-en="Description" data-bn="বিস্তারিত বিবরণ">Description</label>
            <textarea wire:model="description" class="form-control" rows="5"></textarea>
            @error('description') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" wire:model="isAnonymous" class="form-check-input" id="anonCheck">
            <label class="form-check-label" for="anonCheck" data-en="Submit anonymously (your identity will be hidden from the reviewer)" data-bn="অজ্ঞাতনামা হিসেবে জমা দিন (আপনার পরিচয় রিভিউয়ারের কাছে গোপন থাকবে)">
                Submit anonymously (your identity will be hidden from the reviewer)
            </label>
        </div>

        <button wire:click="save" class="btn btn-primary" data-en="Submit" data-bn="জমা দিন">Submit</button>
    </div>
</div>