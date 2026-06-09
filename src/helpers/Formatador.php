<?php

namespace MotoristaCheck\Helpers;

class Formatador
{
    public static function cpf(string $cpf): string
    {
        $cpf = preg_replace('/\D/', '', $cpf);
        if (strlen($cpf) !== 11) return $cpf;
        return substr($cpf, 0, 3) . '.' .
               substr($cpf, 3, 3) . '.' .
               substr($cpf, 6, 3) . '-' .
               substr($cpf, 9, 2);
    }

    public static function cnpj(string $cnpj): string
    {
        $cnpj = preg_replace('/\D/', '', $cnpj);
        if (strlen($cnpj) !== 14) return $cnpj;
        return substr($cnpj, 0, 2) . '.' .
               substr($cnpj, 2, 3) . '.' .
               substr($cnpj, 5, 3) . '/' .
               substr($cnpj, 8, 4) . '-' .
               substr($cnpj, 12, 2);
    }

    public static function telefone(string $tel): string
    {
        $tel = preg_replace('/\D/', '', $tel);
        if (strlen($tel) === 11) {
            return '(' . substr($tel, 0, 2) . ') ' .
                   substr($tel, 2, 1) . ' ' .
                   substr($tel, 3, 4) . '-' .
                   substr($tel, 7, 4);
        }
        if (strlen($tel) === 10) {
            return '(' . substr($tel, 0, 2) . ') ' .
                   substr($tel, 2, 4) . '-' .
                   substr($tel, 6, 4);
        }
        return $tel;
    }

    public static function placa(string $placa): string
    {
        $placa = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $placa));
        if (strlen($placa) === 7) {
            return substr($placa, 0, 3) . '-' . substr($placa, 3, 4);
        }
        return $placa;
    }

    public static function dataHora(string|null $datetime, string $format = 'd/m/Y H:i:s'): string
    {
        if (!$datetime) return '-';
        $dt = new \DateTime($datetime);
        return $dt->format($format);
    }

    public static function data(string|null $datetime, string $format = 'd/m/Y'): string
    {
        if (!$datetime) return '-';
        $dt = new \DateTime($datetime);
        return $dt->format($format);
    }

    public static function hora(string|null $datetime): string
    {
        if (!$datetime) return '-';
        $dt = new \DateTime($datetime);
        return $dt->format('H:i:s');
    }

    public static function tempoDecorrido(string|null $inicio, string|null $fim = null): string
    {
        if (!$inicio) return '-';
        $start = new \DateTime($inicio);
        $end   = $fim ? new \DateTime($fim) : new \DateTime();
        $diff  = $start->diff($end);
        $parts = [];
        if ($diff->h > 0) $parts[] = $diff->h . 'h';
        if ($diff->i > 0) $parts[] = $diff->i . 'min';
        if ($diff->s > 0 && empty($parts)) $parts[] = $diff->s . 's';
        return implode(' ', $parts) ?: '0s';
    }

    public static function statusBadge(string $status): string
    {
        $map = [
            'autorizado'    => 'success',
            'ativo'         => 'success',
            'em_andamento'  => 'primary',
            'pendente'      => 'warning',
            'bloqueado'     => 'danger',
            'recusada'      => 'danger',
            'cancelada'     => 'secondary',
            'finalizada'    => 'info',
        ];
        $class = $map[strtolower($status)] ?? 'secondary';
        return '<span class="badge bg-' . $class . '">' . Security::h($status) . '</span>';
    }

    public static function limitarTexto(string $texto, int $limite = 100): string
    {
        if (mb_strlen($texto) <= $limite) return $texto;
        return mb_substr($texto, 0, $limite) . '...';
    }
}
