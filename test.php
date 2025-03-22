<?php

declare(strict_types=1);

class NantokaController {
    public function index() {
        $user = $this->repository->all();

        return render('index', compact('user'));
    }
}
