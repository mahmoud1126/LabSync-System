<?php require_once __DIR__ . '/../../../includes/header.php'; ?>

<main>
    <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">
                    <i class="bi bi-clipboard-plus-fill text-primary"></i>
                    Create Safety Briefing
                </h2>
                <p class="text-muted mb-0">Define safety protocols for an equipment</p>
            </div>
            <a href="/LabSync-System/admin/briefings" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>

        <?php if (!empty($flash)): ?>
            <div
                class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <i class="bi bi-pencil-square"></i> Briefing Details
            </div>
            <div class="card-body">
                <form method="POST" action="/LabSync-System/admin/briefings/store">

                    <div class="mb-3">
                        <label for="equipmentID" class="form-label fw-semibold">Equipment </label>
                        <select class="form-select" id="equipmentID" name="equipmentID" required>
                            <option value="">— Select Equipment —</option>
                            <?php foreach ($equipment as $eq): ?>
                                <option value="<?= $eq['equipmentID'] ?>">
                                    #<?= $eq['equipmentID'] ?> — <?= htmlspecialchars($eq['equipmentName']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Choose which equipment this briefing applies to.</small>
                    </div>

                    <div class="mb-3">
                        <label for="briefingContent" class="form-label fw-semibold">Briefing Content </label>
                        <textarea class="form-control" id="briefingContent" name="briefingContent" rows="10"
                            placeholder="Enter the full safety protocol text. Researchers must acknowledge this content before starting a session."
                            required></textarea>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="/LabSync-System/admin/briefings" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Save Briefing
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>
</main>

<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>