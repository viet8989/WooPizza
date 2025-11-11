Process auto test and fix bug mini cart

Current bug: when click 'plus' or 'minus' quantity, fields have amount change value wrong(total amount on every item, subtotal, tax, ...)

auto run:

    cmd: open https://terravivapizza.com

sleep 5 seconds

auto call javascript:

    // show mini cart
    document.querySelectorAll('.header-nav.header-nav-main.nav.nav-right.nav-size-large.nav-spacing-xlarge.nav-uppercase li a')[0].click();

    write log(anything you need to fix bug, log server save to /wp-content/debug.log, log client call javascript writeLogServer('{data log}'); save to /wp-content/custom-debug.log);

    sleep 5 seconds

    // plus quantity
    document.querySelector('button.plus').click();    

    // OR minus quantity
    document.querySelector('button.minus').click();   

    sleep 5 seconds

    write log(anything you need to fix bug, log server save to /wp-content/debug.log, log client call javascript writeLogServer('{data log}'); save to /wp-content/custom-debug.log);

    sleep 5 seconds

    you auto read 2 file .log to analyze and edit code to fix bug
    after auto call below cmd to download 2 file log from hosting to local project

    python3 ~/Documents/AutoUploadFTPbyGitStatus/auto_download_file.py /wp-content/debug.log && python3 ~/Documents/AutoUploadFTPbyGitStatus/auto_download_file.py /wp-content/custom-debug.log
