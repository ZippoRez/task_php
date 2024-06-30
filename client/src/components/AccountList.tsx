import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom'; //  Для навигации
import {
  Button, Typography, Pagination, Select, MenuItem, FormControl, InputLabel,
  SelectChangeEvent,
  Container,
} from '@mui/material'; // Компоненты Material-UI
import { Account } from '../types/Account'; // Тип данных Account
import config from '../config'; //  Конфигурация приложения (API URL)
import AccountTable from './AccountTable'; //  Компонент таблицы аккаунтов
import ConfirmationDialog from './ConfirmationDialog'; //  Компонент диалога подтверждения

// Компонент для отображения списка аккаунтов
const AccountList: React.FC = () => {
  // Состояние для хранения списка аккаунтов
  const [accounts, setAccounts] = useState<Account[]>([]);
  // Состояние для индикации загрузки данных
  const [loading, setLoading] = useState(true);
  // Состояние для хранения сообщения об ошибке
  const [error, setError] = useState<string | null>(null);

  // Состояния для пагинации
  const [currentPage, setCurrentPage] = useState(1); //  Текущая страница
  const [accountsPerPage, setAccountsPerPage] = useState(10); //  Количество аккаунтов на странице
  const [totalAccounts, setTotalAccounts] = useState(0); //  Общее количество аккаунтов

  // Состояние для диалога подтверждения удаления
  const [deleteDialogOpen, setDeleteDialogOpen] = useState(false); //  Открыт ли диалог
  const [accountIdToDelete, setAccountIdToDelete] = useState<number | null>(null); //  ID аккаунта для удаления

  //  useEffect для загрузки данных при монтировании компонента и изменении параметров пагинации
  useEffect(() => {
    // Асинхронная функция для загрузки данных 
    const fetchData = async () => {
      try {
        //  Формируем URL запроса с параметрами пагинации
        const response = await fetch(
          `${config.apiUrl}/index.php?page=${currentPage}&limit=${accountsPerPage}&deleted=false`, { //  Загружаем неудаленные аккаунты
            method: 'GET', 
          });

        // Проверяем статус ответа
        if (!response.ok) {
          throw new Error('Ошибка при загрузке данных');
        }

        // Парсим ответ сервера в JSON
        const data = await response.json();

        // Обновляем состояния с полученными данными
        setAccounts(data.data); //  Список аккаунтов
        setTotalAccounts(data.pagination.totalItems); //  Общее количество аккаунтов
      } catch (err) {
        // Обрабатываем ошибки при загрузке данных
        setError('Ошибка при загрузке данных');
        console.error(err);
      } finally {
        // Скрываем индикатор загрузки
        setLoading(false); 
      }
    };

    //  Вызываем функцию загрузки данных
    fetchData();
  }, [currentPage, accountsPerPage]); //  Зависимости useEffect - при изменении currentPage или accountsPerPage данные будут загружены заново

  // Обработчик клика по кнопке "Удалить"
  const handleDelete = (id: number) => {
    // Сохраняем ID аккаунта для удаления
    setAccountIdToDelete(id); 
    // Открываем диалог подтверждения удаления
    setDeleteDialogOpen(true); 
  };

  // Обработчик подтверждения удаления в диалоге
  const handleConfirmDelete = async () => {
    // Закрываем диалог подтверждения
    setDeleteDialogOpen(false); 

    // Проверяем, что ID аккаунта для удаления установлен
    if (accountIdToDelete !== null) {
      try {
        // Отправляем DELETE запрос на сервер для удаления аккаунта
        const response = await fetch(`${config.apiUrl}/delete.php`, {
          method: 'DELETE', 
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: accountIdToDelete }), // Отправляем ID аккаунта в теле запроса
        });

        // Проверяем статус ответа
        if (!response.ok) {
          throw new Error('Ошибка при удалении аккаунта'); 
        }

        // Удаляем аккаунт из списка accounts в состоянии
        setAccounts(accounts.filter((account) => account.id !== accountIdToDelete)); 
      } catch (err) {
        setError('Ошибка при удалении аккаунта');
        console.error(err); 
      } finally {
        // Сбрасываем ID аккаунта для удаления
        setAccountIdToDelete(null); 
      }
    }
  };

  // Обработчик отмены удаления в диалоге
  const handleCancelDelete = () => {
    // Закрываем диалог подтверждения
    setDeleteDialogOpen(false); 
    // Сбрасываем ID аккаунта для удаления
    setAccountIdToDelete(null); 
  };

  // Обработчик изменения страницы пагинации
  const handleChangePage = (event: React.ChangeEvent<unknown>, newPage: number) => {
    // Обновляем состояние currentPage
    setCurrentPage(newPage); 
  };

  // Обработчик изменения количества аккаунтов на странице
  const handleChangeRowsPerPage = (event: SelectChangeEvent<number>) => {
    // Обновляем состояние accountsPerPage
    setAccountsPerPage(Number(event.target.value));
    // Сбрасываем текущую страницу на первую
    setCurrentPage(1); 
  };

  // Если данные загружаются, отображаем сообщение "Загрузка данных..."
  if (loading) {
    return <Typography variant="body1">Загрузка данных...</Typography>; 
  }

  // Если произошла ошибка при загрузке данных, отображаем сообщение об ошибке
  if (error) {
    return <Typography color="error" variant="body1">Ошибка: {error}</Typography>; 
  }

  // Отображение списка аккаунтов
  return (
    <>
      {/* Таблица аккаунтов */}
      <AccountTable accounts={accounts} onDelete={handleDelete} /> 

      {/* Диалог подтверждения удаления */}
      <ConfirmationDialog
        open={deleteDialogOpen} //  Отображается, если deleteDialogOpen === true
        onClose={handleCancelDelete} //  Вызывается при закрытии диалога
        onConfirm={handleConfirmDelete} //  Вызывается при подтверждении удаления
        title="Подтверждение удаления" 
        message="Вы уверены, что хотите удалить этот аккаунт?" 
      />

      {/* Блок с пагинацией, выбором количества аккаунтов на странице и кнопками навигации */}
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginTop: '20px' }}>
        {/* Выбор количества аккаунтов на странице */}
        <FormControl variant="filled" sx={{ width: 1/10 }} >
          <InputLabel id="rows-per-page-label">Аккаунтов на странице</InputLabel>
          <Select
            labelId="rows-per-page-label"
            id="rows-per-page"
            value={accountsPerPage} 
            onChange={handleChangeRowsPerPage} 
            label="Аккаунтов на странице" 
          >
            <MenuItem value={10}>10</MenuItem>
            <MenuItem value={25}>25</MenuItem>
            <MenuItem value={50}>50</MenuItem>
            <MenuItem value={100}>100</MenuItem>
          </Select>
        </FormControl>

        {/* Кнопки навигации */}
        <div>
          <Container>
            <Button component={Link} to="/" variant="contained" color="primary" >
              Список аккаунтов 
            </Button>
            <Button component={Link} to="/companies" variant="contained" color="primary" style={{ marginLeft: '10px' }}>
              Список компаний
            </Button>
            <Button component={Link} to="/create" variant="contained" color="secondary" style={{ marginLeft: '10px' }}>
              Создать аккаунт 
            </Button>
            <Button component={Link} to="/trash" variant="contained" color="error" style={{ marginLeft: '10px' }}>
              Удаленные аккаунты 
            </Button>
          </Container>
        </div>

        {/* Пагинация */}
        <Pagination 
          count={Math.ceil(totalAccounts / accountsPerPage)} // Вычисляем количество страниц
          page={currentPage} //  Текущая страница
          onChange={handleChangePage} //  Обработчик изменения страницы
          variant="outlined" shape="rounded" showFirstButton showLastButton 
        />
      </div>
    </>
  );
};

export default AccountList;