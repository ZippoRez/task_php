import React, { useState, useEffect } from 'react'; // Импорт необходимых хуков и компонентов из React
import { Link } from 'react-router-dom'; // Импорт компонента Link для навигации
import {
  // Импорт компонентов Material-UI для таблицы, кнопок, текста и других элементов интерфейса
  Table, TableBody, TableCell, TableContainer, TableHead, TableRow,
  Paper, Button, Typography, Pagination, Select, MenuItem, FormControl, InputLabel,
  SelectChangeEvent, Dialog, DialogTitle, DialogContent, DialogActions,
} from '@mui/material';
import config from '../config'; // Импорт конфигурации приложения

// Интерфейс для описания объекта "Компания"
interface Company {
  id: number; // ID компании
  name: string; // Название компании
  address: string; // Адрес компании
}

// Компонент для отображения списка компаний
const CompanyList: React.FC = () => {
  // Состояние для хранения списка компаний
  const [companies, setCompanies] = useState<Company[]>([]);

  // Состояния для управления загрузкой данных
  const [loading, setLoading] = useState(true); // Флаг загрузки данных
  const [error, setError] = useState<string | null>(null); // Состояние для хранения сообщения об ошибке

  // Состояния для пагинации
  const [currentPage, setCurrentPage] = useState(1); // Текущая страница
  const [companiesPerPage, setCompaniesPerPage] = useState(10); // Количество компаний на странице
  const [totalCompanies, setTotalCompanies] = useState(0); // Общее количество компаний

  // Состояние для управления диалоговым окном подтверждения удаления
  const [deleteDialogOpen, setDeleteDialogOpen] = useState(false); // Флаг видимости диалога
  const [companyIdToDelete, setCompanyIdToDelete] = useState<number | null>(null); // ID компании, которую нужно удалить

  // Хук useEffect для загрузки данных при монтировании компонента и при изменении страницы или количества компаний на странице
  useEffect(() => {
    // Асинхронная функция для загрузки данных с сервера
    const fetchData = async () => {
      try {
        // Отправка GET-запроса на сервер для получения списка компаний
        const response = await fetch(`${config.apiUrl}/companies.php?page=${currentPage}&limit=${companiesPerPage}`);
        // Проверка ответа сервера
        if (!response.ok) {
          throw new Error('Ошибка при загрузке данных');
        }
        // Парсинг JSON-ответа от сервера
        const data = await response.json();
        // Обновление состояния компаний, полученных с сервера
        setCompanies(data.data);
        // Обновление состояния общего количества компаний
        setTotalCompanies(data.pagination.totalItems);
      } catch (err) {
        // Обработка ошибок при загрузке данных
        setError('Ошибка при загрузке данных');
        console.error(err);
      } finally {
        // Сброс флага загрузки данных
        setLoading(false);
      }
    };
    // Вызов функции загрузки данных
    fetchData();
  }, [currentPage, companiesPerPage]); // Зависимости хука useEffect: currentPage, companiesPerPage

  // Обработчик события клика по кнопке "Удалить"
  const handleDelete = (id: number) => {
    // Сохранение ID компании, которую нужно удалить
    setCompanyIdToDelete(id);
    // Открытие диалогового окна подтверждения удаления
    setDeleteDialogOpen(true);
  };

  // Обработчик события подтверждения удаления компании
  const handleConfirmDelete = async () => {
    // Закрытие диалогового окна подтверждения удаления
    setDeleteDialogOpen(false);

    // Проверка, что ID компании для удаления не равен null
    if (companyIdToDelete !== null) {
      try {
        // Отправка DELETE-запроса на сервер для удаления компании
        const response = await fetch(`${config.apiUrl}/company.php?id=${companyIdToDelete}`, {
          method: 'DELETE',
        });

        // Проверка ответа сервера
        if (!response.ok) {
          throw new Error('Ошибка при удалении компании');
        }

        // Обновление списка компаний, удаляя компанию с указанным ID
        setCompanies(companies.filter(company => company.id !== companyIdToDelete)); 
      } catch (err) {
        // Обработка ошибок при удалении компании
        setError('Ошибка при удалении компании');
        console.error(err);
      } finally {
        // Сброс ID компании для удаления
        setCompanyIdToDelete(null);
      }
    }
  };

  // Обработчик события отмены удаления компании
  const handleCancelDelete = () => {
    // Закрытие диалогового окна подтверждения удаления
    setDeleteDialogOpen(false);
    // Сброс ID компании для удаления
    setCompanyIdToDelete(null);
  };

  // Обработчик события изменения страницы пагинации
  const handleChangePage = (event: React.ChangeEvent<unknown>, newPage: number) => {
    // Обновление состояния текущей страницы
    setCurrentPage(newPage);
  };

  // Обработчик события изменения количества компаний на странице
  const handleChangeRowsPerPage = (event: SelectChangeEvent<number>) => {
    // Обновление состояния количества компаний на странице
    setCompaniesPerPage(Number(event.target.value));
    // Сброс текущей страницы на первую
    setCurrentPage(1); 
  };

  // Отображение сообщения о загрузке данных, если данные еще загружаются
  if (loading) {
    return <Typography variant="body1">Загрузка данных...</Typography>;
  }

  // Отображение сообщения об ошибке, если произошла ошибка при загрузке данных
  if (error) {
    return <Typography color="error" variant="body1">Ошибка: {error}</Typography>;
  }

  // Отображение списка компаний, если данные загружены успешно
  return (
    <>
      {/* Контейнер для таблицы */}
      <TableContainer component={Paper}>
        {/* Таблица */}
        <Table>
          {/* Заголовок таблицы */}
          <TableHead>
            <TableRow>
              <TableCell>ID</TableCell>
              <TableCell>Название</TableCell>
              <TableCell>Адрес</TableCell>
              <TableCell>Действия</TableCell>
            </TableRow>
          </TableHead>
          {/* Тело таблицы */}
          <TableBody>
            {/* Отображение строк таблицы для каждой компании */}
            {companies.map((company) => (
              <TableRow key={company.id}>
                {/* Ячейка с ID компании */}
                <TableCell>{company.id}</TableCell>
                {/* Ячейка с названием компании */}
                <TableCell>{company.name}</TableCell>
                {/* Ячейка с адресом компании */}
                <TableCell>{company.address}</TableCell>
                {/* Ячейка с кнопками действий */}
                <TableCell>
                  {/* Кнопка "Просмотреть" */}
                  <Button component={Link} to={`/companies/${company.id}`}>
                    Просмотреть
                  </Button>
                  {/* Кнопка "Редактировать" */}
                  <Button component={Link} to={`/companies/edit/${company.id}`}>
                    Редактировать
                  </Button>
                  {/* Кнопка "Удалить" */}
                  <Button onClick={() => handleDelete(company.id)} color="error">
                    Удалить
                  </Button>
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </TableContainer>

      {/* Контейнер для элементов управления пагинацией */}
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginTop: '20px' }}>
        {/* Выпадающий список для выбора количества компаний на странице */}
        <FormControl variant="filled" sx={{ width: 1/10 }}>
          <InputLabel id="companies-per-page-label">Компаний на странице</InputLabel>
          <Select
            labelId="companies-per-page-label"
            id="companies-per-page"
            value={companiesPerPage}
            onChange={handleChangeRowsPerPage}
            label="Компаний на странице"
          >
            <MenuItem value="10">10</MenuItem>
            <MenuItem value="25">25</MenuItem>
            <MenuItem value="50">50</MenuItem>
          </Select>
        </FormControl>
        {/* Контейнер для кнопок "Список аккаунтов" и "Создать компанию" */}
        <div style={{ marginTop: '20px' }}>
        {/* Кнопка "Список аккаунтов" */}
        <Button component={Link} to="/" variant="contained" color="primary" style={{ marginLeft: '10px' }}>
            Список аккаунтов
          </Button>
        {/* Кнопка "Создать компанию" */}
        <Button component={Link} to="/companies/create" variant="contained" color="secondary" style={{ marginLeft: '10px' }}>
          Создать компанию
        </Button>
      </div>
        {/* Компонент пагинации */}
        <Pagination count={Math.ceil(totalCompanies / companiesPerPage)} page={currentPage} onChange={handleChangePage} />
        
      </div>

      {/* Диалоговое окно подтверждения удаления */}
      <Dialog open={deleteDialogOpen} onClose={handleCancelDelete}>
        {/* Заголовок диалогового окна */}
        <DialogTitle>Подтверждение удаления</DialogTitle>
        {/* Содержимое диалогового окна */}
        <DialogContent>
          <Typography>Вы уверены, что хотите удалить эту компанию?</Typography>
        </DialogContent>
        {/* Кнопки действий диалогового окна */}
        <DialogActions>
          <Button onClick={handleCancelDelete}>Отмена</Button>
          <Button onClick={handleConfirmDelete} color="error">
            Удалить
          </Button>
        </DialogActions>
      </Dialog>
    </>
  );
};

// Экспорт компонента CompanyList по умолчанию
export default CompanyList;