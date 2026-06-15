<!-- resources/views/livewire/super-admin/settings/system-settings-component.blade.php -->

<div class="p-6 space-y-6">

    <h2 class="text-xl font-bold">⚙️ System Settings</h2>

    @if(session()->has('success'))
        <div class="bg-green-100 text-green-700 p-2 rounded">
            {{ session('success') }}
        </div>
    @endif

    <!-- App Settings -->
    <div class="bg-white p-4 rounded shadow">
        <h3 class="font-bold mb-2">App Settings</h3>

        <input type="text" wire:model="app_name" class="w-full border p-2" placeholder="App Name">
    </div>

    <!-- SMTP -->
    <div class="bg-white p-4 rounded shadow">
        <h3 class="font-bold mb-2">SMTP Settings</h3>

        <input wire:model="smtp_host" class="w-full border p-2 mb-2" placeholder="SMTP Host">
        <input wire:model="smtp_port" class="w-full border p-2 mb-2" placeholder="SMTP Port">
        <input wire:model="smtp_user" class="w-full border p-2 mb-2" placeholder="SMTP User">
        <input wire:model="smtp_pass" class="w-full border p-2" placeholder="SMTP Password">
    </div>

    <!-- Gateway -->
    <div class="bg-white p-4 rounded shadow">
        <h3 class="font-bold mb-2">Gateway</h3>

        <input wire:model="sms_gateway" class="w-full border p-2 mb-2" placeholder="SMS Gateway">
        <input wire:model="payment_gateway" class="w-full border p-2" placeholder="Payment Gateway">
    </div>

    <!-- Feature Toggle -->
    <div class="bg-white p-4 rounded shadow">
        <h3 class="font-bold mb-2">Feature Control</h3>

        <label><input type="checkbox" wire:model="feature_student"> Student Module</label><br>
        <label><input type="checkbox" wire:model="feature_teacher"> Teacher Module</label><br>
        <label><input type="checkbox" wire:model="feature_fee"> Fee Module</label><br>
    </div>

    <!-- Maintenance -->
    <div class="bg-white p-4 rounded shadow">
        <label>
            <input type="checkbox" wire:model="maintenance_mode">
            Maintenance Mode
        </label>
    </div>

    <!-- Save -->
    <button wire:click="save"
        class="bg-blue-600 text-white px-4 py-2 rounded">
        Save Settings
    </button>

</div>