import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import {
    Button, Typography, Pagination, Select, MenuItem, FormControl, InputLabel,
    SelectChangeEvent,
    Container,
} from '@mui/material';
import { Account } from '../types/Account';
import config from '../config';
import AccountTable from './AccountTable';
import ConfirmationDialog from './ConfirmationDialog';

const Trash: React.FC = () => {
  const [accounts, setAccounts] = useState<Account[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Состояния для пагинации
  const [currentPage, setCurrentPage] = useState(1);
  const [accountsPerPage, setAccountsPerPage] = useState(10); 
  const [totalAccounts, setTotalAccounts] = useState(0);

  //  Состояние для диалога подтверждения
  const [deleteDialogOpen, setDeleteDialogOpen] = useState(false);
  const [accountIdToDelete, setAccountIdToDelete] = useState<number | null>(null);

  useEffect(() => {
    const fetchData = async () => {
      try {
        const response = await fetch(`${config.apiUrl}/index.php?page=${currentPage}&limit=${accountsPerPage}&deleted=true`); // Запрос удаленных аккаунтов
        if (!response.ok) {
          throw new Error('Ошибка при загрузке данных');
        }
        const data = await response.json();
        setAccounts(data.data);
        setTotalAccounts(data.pagination.totalItems); 
      } catch (err) {
        setError('Ошибка при загрузке данных');
        console.error(err);
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, [currentPage, accountsPerPage]);

  const handleRestore = async (id: number) => {
    try {
        const response = await fetch(`${config.apiUrl}/restore.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id }),
        });

        if (!response.ok) {
            throw new Error('Ошибка при восстановлении аккаунта');
        }
        // Обновляем список аккаунтов после восстановления
        setAccounts(accounts.filter(account => account.id !== id));
    } catch (err) {
        setError('Ошибка при восстановлении аккаунта');
        console.error(err);
    }
  };

  const handleDelete = (id: number) => {
    //  Открываем диалог подтверждения
    setAccountIdToDelete(id);
    setDeleteDialogOpen(true);
  };

  const handleConfirmDelete = async () => {
    //  Закрываем диалог
    setDeleteDialogOpen(false);

    if (accountIdToDelete !== null) {
      try {
        const response = await fetch(`${config.apiUrl}/delete.php`, {
          method: 'DELETE',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: accountIdToDelete, permanent: true }),
        });

        if (!response.ok) {
          throw new Error('Ошибка при удалении аккаунта');
        }

        setAccounts(accounts.filter((account) => account.id !== accountIdToDelete));
      } catch (err) {
        setError('Ошибка при удалении аккаунта');
        console.error(err);
      } finally {
        setAccountIdToDelete(null); // Сбрасываем ID после удаления
      }
    }
  };

  const handleCancelDelete = () => {
    setDeleteDialogOpen(false);
    setAccountIdToDelete(null);
  };

  const handleChangePage = (event: React.ChangeEvent<unknown>, newPage: number) => {
    setCurrentPage(newPage);
  };

  const handleChangeRowsPerPage = (event: SelectChangeEvent<number>) => {
    setAccountsPerPage(Number(event.target.value));
    setCurrentPage(1);
  };

  if (loading) {
    return <Typography variant="body1">Загрузка данных...</Typography>;
  }

  if (error) {
    return <Typography color="error" variant="body1">Ошибка: {error}</Typography>;
  }

  return (
    <>
      <AccountTable accounts={accounts} onDelete={handleDelete} onRestore={handleRestore} />

      <div
        style={{
          display: 'flex',
          justifyContent: 'space-between',
          alignItems: 'center',
          marginTop: '20px',
        }}
      >
        <FormControl variant="standard" sx={{ minWidth: 120 }}>
          <InputLabel id="rows-per-page-label">Аккаунтов на странице</InputLabel>
          <Select
            labelId="rows-per-page-label"
            id="rows-per-page"
            value={accountsPerPage}
            onChange={handleChangeRowsPerPage}
            label="Аккаунтов на странице"
          >
            <MenuItem value="10">10</MenuItem>
            <MenuItem value="25">25</MenuItem>
            <MenuItem value="50">50</MenuItem>
          </Select>
        </FormControl>
        <div >
          <Container >
          <Button component={Link} to="/" variant="contained" color="primary" >
            Список аккаунтов
          </Button>
          <Button component={Link} to="/create" variant="contained" color="secondary" style={{ marginLeft: '10px' }}>
            Создать аккаунт
          </Button>
          </Container>
        </div>
        <Pagination
          count={Math.ceil(totalAccounts / accountsPerPage)}
          page={currentPage}
          onChange={handleChangePage}
        />
      </div>

      {/* Диалог подтверждения */}
      <ConfirmationDialog
        open={deleteDialogOpen}
        onClose={handleCancelDelete}
        onConfirm={handleConfirmDelete}
        title="Подтверждение удаления"
        message="Вы уверены, что хотите удалить этот аккаунт?"
      />
    </>
  );
};

export default Trash;