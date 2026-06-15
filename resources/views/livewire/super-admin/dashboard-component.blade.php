<div class="container py-4">

    <div class="row g-3">

        <!-- Total Schools -->
        <div class="col-md-3 col-sm-6">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <h6>Total Schools</h6>
                    <h3 class="text-primary">{{ $totalSchools }}</h3>
                </div>
            </div>
        </div>

        <!-- Active Schools -->
        <div class="col-md-3 col-sm-6">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <h6>Active Schools</h6>
                    <h3 class="text-success">{{ $activeSchools }}</h3>
                </div>
            </div>
        </div>

        <!-- Students -->
        <div class="col-md-3 col-sm-6">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <h6>Total Students</h6>
                    <h3 class="text-info">{{ $totalStudents }}</h3>
                </div>
            </div>
        </div>

        <!-- Teachers -->
        <div class="col-md-3 col-sm-6">
            <div class="card shadow-sm border-0">
                <div class="card-body text-center">
                    <h6>Total Teachers</h6>
                    <h3 class="text-warning">{{ $totalTeachers }}</h3>
                </div>
            </div>
        </div>

        <!-- Revenue -->
        <div class="col-md-12">
            <div class="card shadow-sm border-0 mt-2">
                <div class="card-body text-center">
                    <h6>Total Revenue</h6>
                    <h2 class="text-danger">
                        {{ number_format($totalRevenue, 2) }} ৳
                    </h2>
                </div>
            </div>
        </div>

    </div>
</div>