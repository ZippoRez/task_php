import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom'; //  Для навигации
import {
  Button, Typography, Pagination, Select, MenuItem, FormControl, InputLabel,
  SelectChangeEvent,
  Container,
} from '@mui/material'; //  Компоненты Material-UI
import { Account } from '../types/Account'; //  Тип данных Account
import config from '../config'; //  Конфигурация приложения (API URL)
import AccountTable from './AccountTable'; //  Компонент таблицы аккаунтов
import ConfirmationDialog from './ConfirmationDialog'; //  Компонент диалога подтверждения

//  Компонент для отображения списка удаленных аккаунтов ("Корзина")
const Trash: React.FC = () => {
  //  Состояние для хранения списка удаленных аккаунтов
  const [accounts, setAccounts] = useState<Account[]>([]); 
  //  Состояние для индикации загрузки данных
  const [loading, setLoading] = useState(true); 
  //  Состояние для хранения сообщения об ошибке
  const [error, setError] = useState<string | null>(null); 

  //  Состояния для пагинации
  const [currentPage, setCurrentPage] = useState(1); //  Текущая страница
  const [accountsPerPage, setAccountsPerPage] = useState(10); //  Количество аккаунтов на странице
  const [totalAccounts, setTotalAccounts] = useState(0); //  Общее количество аккаунтов

  //  Состояние для диалога подтверждения окончательного удаления аккаунта
  const [deleteDialogOpen, setDeleteDialogOpen] = useState(false); //  Открыт ли диалог
  const [accountIdToDelete, setAccountIdToDelete] = useState<number | null>(null); //  ID аккаунта для удаления

  //  useEffect для загрузки списка удаленных аккаунтов при монтировании компонента 
  //  и при изменении параметров пагинации
  useEffect(() => {
    //  Асинхронная функция для загрузки данных
    const fetchData = async () => {
      try {
        //  Отправляем GET запрос на сервер для получения удаленных аккаунтов
        const response = await fetch(`${config.apiUrl}/index.php?page=${currentPage}&limit=${accountsPerPage}&deleted=true`); 
        if (!response.ok) {
          throw new Error('Ошибка при загрузке данных');
        }

        //  Парсим ответ сервера в JSON
        const data = await response.json();

        //  Обновляем состояния
        setAccounts(data.data); //  Список аккаунтов
        setTotalAccounts(data.pagination.totalItems); //  Общее количество
      } catch (err) {
        setError('Ошибка при загрузке данных');
        console.error(err); 
      } finally {
        setLoading(false); 
      }
    };

    fetchData(); 
  }, [currentPage, accountsPerPage]); //  Зависимости useEffect

  //  Обработчик нажатия на кнопку "Восстановить"
  const handleRestore = async (id: number) => {
    try {
      //  Отправляем POST запрос на сервер для восстановления аккаунта
      const response = await fetch(`${config.apiUrl}/restore.php`, { 
        method: 'POST',
        headers: { 'Content-Type': 'application/json' }, 
        body: JSON.stringify({ id: id }), 
      });

      //  Проверка ответа сервера
      if (!response.ok) {
        throw new Error('Ошибка при восстановлении аккаунта');
      }

      //  Обновляем список аккаунтов,  удаляя восстановленный аккаунт
      setAccounts(accounts.filter(account => account.id !== id)); 
    } catch (err) {
      setError('Ошибка при восстановлении аккаунта');
      console.error(err);
    }
  };

  //  Обработчик нажатия на кнопку "Удалить"
  const handleDelete = (id: number) => {
    setAccountIdToDelete(id);
    setDeleteDialogOpen(true); 
  };

  //  Обработчик подтверждения окончательного удаления в диалоге
  const handleConfirmDelete = async () => {
    setDeleteDialogOpen(false); 

    if (accountIdToDelete !== null) {
      try {
        //  Отправляем DELETE запрос на сервер для окончательного удаления аккаунта
        const response = await fetch(`${config.apiUrl}/delete.php`, { 
          method: 'DELETE', 
          headers: { 'Content-Type': 'application/json' }, 
          body: JSON.stringify({ id: accountIdToDelete, permanent: true }), //  Указываем permanent: true для окончательного удаления
        });

        if (!response.ok) {
          throw new Error('Ошибка при удалении аккаунта');
        }

        //  Обновляем список аккаунтов, удаляя окончательно удаленный аккаунт
        setAccounts(accounts.filter((account) => account.id !== accountIdToDelete)); 
      } catch (err) {
        setError('Ошибка при удалении аккаунта');
        console.error(err); 
      } finally {
        setAccountIdToDelete(null);
      }
    }
  };

  //  Обработчик отмены удаления в диалоге
  const handleCancelDelete = () => {
    setDeleteDialogOpen(false); 
    setAccountIdToDelete(null);
  };

  //  Обработчик изменения страницы пагинации
  const handleChangePage = (event: React.ChangeEvent<unknown>, newPage: number) => {
    setCurrentPage(newPage); 
  };

  //  Обработчик изменения количества аккаунтов на странице
  const handleChangeRowsPerPage = (event: SelectChangeEvent<number>) => {
    setAccountsPerPage(Number(event.target.value));
    setCurrentPage(1); 
  };

  //  Пока данные загружаются,  отображаем сообщение
  if (loading) {
    return <Typography variant="body1">Загрузка данных...</Typography>; 
  }

  //  Если произошла ошибка при загрузке данных,  отображаем сообщение об ошибке
  if (error) {
    return <Typography color="error" variant="body1">Ошибка: {error}</Typography>; 
  }

  return (
    <>
      {/*  Отображаем таблицу удаленных аккаунтов, передаем обработчики  */}
      <AccountTable accounts={accounts} onDelete={handleDelete} onRestore={handleRestore} /> 

      {/*  Блок с пагинацией,  выбором количества аккаунтов на странице и кнопками  */}
      <div
        style={{
          display: 'flex',
          justifyContent: 'space-between',
          alignItems: 'center',
          marginTop: '20px',
        }}
      >
        {/*  Выбор количества аккаунтов на странице  */}
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

        {/* Кнопки навигации */}
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

        {/*  Пагинация  */}
        <Pagination
          count={Math.ceil(totalAccounts / accountsPerPage)} 
          page={currentPage} 
          onChange={handleChangePage} 
        />
      </div>

      {/*  Диалог подтверждения окончательного удаления  */}
      <ConfirmationDialog 
        open={deleteDialogOpen} 
        onClose={handleCancelDelete}
        onConfirm={handleConfirmDelete}
        title="Подтверждение удаления" 
        message="Вы уверены, что хотите удалить этот аккаунт НАВСЕГДА?" 
      />
    </>
  );
};

export default Trash;