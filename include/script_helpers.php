<?php
function print_success($message) {
  print "\033[1;32m*\033[0m $message\n";
}

function print_error($message) {
  print "\033[1;31m*\033[0m $message\n";
}
?>