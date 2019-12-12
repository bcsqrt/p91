        <div class="row">
            <!-- Footer -->

            <div class="col-sm-3 bg-dark p-2 rounded-bottom order-md-1 text-secondary text-center">
             <?php
                printf(
                    "Total time: %s\r\nMemory Used (current): %s\r\nMemory Used (max): %s", 
                    round(microtime(true) - $start, 4), 
                    formatBytes(memory_get_usage()), 
                    formatBytes(memory_get_peak_usage())
                );
                echo '<br> Pre-Alfa V.0.1.';
             ?>
            </div>
  
        </div>
    </div>

    
</body>
<!-- Body Tag -->

</html>