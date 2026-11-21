<?php
require_once __DIR__ . '/KontrakPresenterLowongan.php';
require_once __DIR__ . '/../models/KontrakModelLowongan.php';
require_once __DIR__ . '/../views/KontrakViewLowongan.php';

class PresenterLowongan implements KontrakPresenterLowongan {

    private KontrakModelLowongan $model;
    private KontrakViewLowongan $view;

    public function __construct(KontrakModelLowongan $model, KontrakViewLowongan $view) {
        $this->model = $model;
        $this->view = $view;
    }

    private function buildStatusOptionsHtml(string $selected): string {
        $listStatus = ['Dibuka', 'Ditutup'];
        $optionsHtml = '';
        foreach ($listStatus as $status) {
            $isSelected = ($status === $selected) ? ' selected' : '';
            $optionsHtml .= "<option value='{$status}'{$isSelected}>{$status}</option>";
        }
        return $optionsHtml;
    }

    private function normalizeStatus(string $status): string {
        return ($status === 'Ditutup') ? 'Ditutup' : 'Dibuka';
    }

    private function buildRowHtml(int $no, Lowongan $lowongan): string {
        $id = (int)$lowongan->getId();
        $judul = htmlspecialchars($lowongan->getJudul());
        $departemen = htmlspecialchars($lowongan->getDepartemen());
        $lokasi = htmlspecialchars($lowongan->getLokasi());
        $kuota = number_format($lowongan->getKuota()) . ' Orang';
        $status = htmlspecialchars($lowongan->getStatus());

        return "<tr>
            <td class='col-id'>{$no}</td>
            <td><strong>{$judul}</strong></td>
            <td>{$departemen}</td>
            <td>{$lokasi}</td>
            <td>{$kuota}</td>
            <td>{$status}</td>
            <td class='col-actions'>
                <a href='lowongan.php?screen=edit&id={$id}' class='btn btn-edit'>Edit</a>
                <form method='post' action='lowongan.php' style='display:inline;' onsubmit=\"return confirm('Hapus lowongan ini?')\">
                    <input type='hidden' name='action' value='hapus'>
                    <input type='hidden' name='id' value='{$id}'>
                    <button type='submit' class='btn btn-delete'>Hapus</button>
                </form>
            </td>
        </tr>";
    }

    public function tampilkanLowongan(): string {
        $listLowongan = $this->model->getAllLowongan();
        $totalLowongan = count($listLowongan);

        $rowsHtml = '';
        if (empty($listLowongan)) {
            $rowsHtml = '<tr><td colspan="7" style="text-align:center;color:#6b7280;padding:24px;">Belum ada data lowongan.</td></tr>';
        } else {
            $no = 1;
            foreach ($listLowongan as $lowongan) {
                $rowsHtml .= $this->buildRowHtml($no, $lowongan);
                $no++;
            }
        }

        return $this->view->renderDaftar($rowsHtml, $totalLowongan);
    }

    public function tampilkanFormLowongan(?int $id = null): string {
        if ($id !== null) {
            $lowongan = $this->model->getLowonganById($id);
            if ($lowongan === null) {
                header("Location: lowongan.php");
                exit();
            }

            $formData = [
                'id'                => $lowongan->getId(),
                'judul'             => $lowongan->getJudul(),
                'departemen'        => $lowongan->getDepartemen(),
                'lokasi'            => $lowongan->getLokasi(),
                'kuota'             => $lowongan->getKuota(),
                'statusOptionsHtml' => $this->buildStatusOptionsHtml($lowongan->getStatus()),
            ];
            return $this->view->renderForm("Ubah Data Lowongan", "ubah", $formData);
        }

        $formData = [
            'statusOptionsHtml' => $this->buildStatusOptionsHtml('Dibuka'),
        ];
        return $this->view->renderForm("Tambah Lowongan Baru", "tambah", $formData);
    }

    public function tambahLowongan(string $judul, string $departemen, string $lokasi, int $kuota, string $status): void {
        $this->model->addLowongan($judul, $departemen, $lokasi, $kuota, $this->normalizeStatus($status));
        header("Location: lowongan.php");
        exit();
    }

    public function ubahLowongan(int $id, string $judul, string $departemen, string $lokasi, int $kuota, string $status): void {
        $this->model->updateLowongan($id, $judul, $departemen, $lokasi, $kuota, $this->normalizeStatus($status));
        header("Location: lowongan.php");
        exit();
    }

    public function hapusLowongan(int $id): void {
        $this->model->deleteLowongan($id);
        header("Location: lowongan.php");
        exit();
    }
}
?>