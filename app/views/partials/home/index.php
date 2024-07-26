<?php 
$page_id = null;
$comp_model = new SharedController;
$current_page = $this->set_current_page_link();
?>
<div>
    <div  class="bg-light p-3 mb-3">
        <div class="container">
            <div class="row ">
                <div class="col-md-12 comp-grid">
                    <h4 >The Dashboard</h4>
                </div>
            </div>
        </div>
    </div>
    <div  class="">
        <div class="container">
            <div class="row ">
                <div class="col-sm-4 comp-grid">
                    <?php $rec_count = $comp_model->getcount_lakilaki();  ?>
                    <a class="animated zoomIn record-count card bg-secondary text-white"  href="<?php print_link("tabsiswa/") ?>">
                        <div class="row">
                            <div class="col-2">
                                <i class="material-icons ">brightness_1</i>
                            </div>
                            <div class="col-10">
                                <div class="flex-column justify-content align-center">
                                    <div class="title">Laki - laki</div>
                                    <small class=""></small>
                                </div>
                            </div>
                            <h4 class="value"><strong><?php echo $rec_count; ?></strong></h4>
                        </div>
                    </a>
                </div>
                <div class="col-sm-4 comp-grid">
                    <?php $rec_count = $comp_model->getcount_perempuan();  ?>
                    <a class="animated zoomIn record-count card bg-secondary text-white"  href="<?php print_link("tabsiswa/") ?>">
                        <div class="row">
                            <div class="col-2">
                                <i class="material-icons ">brightness_7</i>
                            </div>
                            <div class="col-10">
                                <div class="flex-column justify-content align-center">
                                    <div class="title">Perempuan</div>
                                    <small class=""></small>
                                </div>
                            </div>
                            <h4 class="value"><strong><?php echo $rec_count; ?></strong></h4>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
