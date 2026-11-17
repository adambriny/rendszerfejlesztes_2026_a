<?php
// A projekt gyökeréből a nyilvános weboldalra irányít, így a http://localhost/kamatbonusz/ cím is működik.
header('Location: public/', true, 302);
exit;
