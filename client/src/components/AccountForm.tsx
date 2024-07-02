import React, { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import {
  TextField, Button, Container, Typography, Grid, FormControl, InputLabel, Select, MenuItem, Dialog, DialogTitle, DialogContent, DialogActions,
} from '@mui/material';
import { Account } from '../types/Account';
import InputTextField from './InputTextField';
import config from '../config';
import { Company } from '../types/Company';
import { SelectChangeEvent } from '@mui/material';

// Интерфейс для пропсов компонента AccountForm
interface AccountFormProps {
  mode: 'create' | 'edit'; // Режим формы: создание или редактирование
  accountId?: number; // ID аккаунта (используется в режиме редактирования)
}

// Компонент AccountForm для создания и редактирования аккаунтов
const AccountForm: React.FC<AccountFormProps> = ({ mode, accountId }) => {
  // Хук для перенаправления
  const navigate = useNavigate();
  // Хук для доступа к параметрам URL (используется только в режиме редактирования)
  const { id } = useParams<{ id: string }>(); 

  // Состояние для хранения данных формы (данные аккаунта)
  const [formData, setFormData] = useState<Account>({
    id: 0,
    first_name: '',
    last_name: '',
    email: '',
    position: '',
    phone_1: '',
    phone_2: '',
    phone_3: '',
    company_id: null,
    deleted_at: '',
  });

  // Состояния для работы с компаниями
  const [companies, setCompanies] = useState<Company[]>([]); // Список компаний
  const [newCompanyName, setNewCompanyName] = useState(''); // Название новой компании
  const [newCompanyAddress, setNewCompanyAddress] = useState(''); // Адрес новой компании
  const [showCompanyDialog, setShowCompanyDialog] = useState(false); // Флаг для отображения диалога создания компании
  
  // Состояние для хранения сообщения об ошибке
  const [error, setError] = useState<string | null>(null);

  // Эффект для загрузки данных аккаунта в режиме редактирования
  useEffect(() => {
    if (mode === 'edit' && accountId) {
      const fetchData = async () => {
        try {
          // Запрос данных аккаунта по ID
          const response = await fetch(`${config.apiUrl}/index.php?id=${accountId}`); 
          // Проверка успешности запроса
          if (!response.ok) {
            throw new Error('Ошибка при загрузке данных аккаунта'); 
          }
          // Парсинг JSON ответа
          const data = await response.json(); 
          // Обновление состояния данными аккаунта
          setFormData(data.data); 
        } catch (err) {
          // Обработка ошибки при загрузке данных
          setError('Ошибка при загрузке данных аккаунта'); 
          console.error(err); 
        }
      };
      // Вызов функции для загрузки данных
      fetchData(); 
    }
  }, [mode, accountId]); 

  // Эффект для загрузки списка компаний при монтировании компонента
  useEffect(() => {
    const fetchCompanies = async () => {
      try {
        // Запрос списка компаний
        const response = await fetch(`${config.apiUrl}/companies.php`); 
        if (!response.ok) {
          throw new Error('Ошибка при загрузке списка компаний'); 
        }
        // Парсинг JSON ответа
        const data = await response.json();
        // Обновление состояния списком компаний
        setCompanies(data.data); 
      } catch (err) {
        // Обработка ошибки при загрузке списка компаний
        setError('Ошибка при загрузке списка компаний'); 
        console.error(err); 
      }
    };

    // Вызов функции для загрузки списка компаний
    fetchCompanies(); 
  }, []);

  // Обработчик изменения значений в полях формы
  const handleChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    // Обновляем состояние formData, изменяя значение соответствующего поля
    setFormData({
      ...formData,
      [event.target.name]: event.target.value,
    });
  };

  // Обработчик изменения значения в Select для выбора компании
  const handleCompanyChange = (event: SelectChangeEvent<number>) => {
    // Получаем ID выбранной компании
    const companyId = event.target.value !== '' ? Number(event.target.value) : null;
    // Обновляем состояние formData, устанавливая ID выбранной компании
    setFormData({ ...formData, company_id: companyId });
  };

  // Обработчик отправки формы
  const handleSubmit = async (event: React.FormEvent) => {
    // Предотвращаем стандартное поведение формы
    event.preventDefault();
    // Сбрасываем сообщение об ошибке
    setError(null);

    // Определяем URL и метод запроса в зависимости от mode (create или edit)
    const url = mode === 'create' ? `${config.apiUrl}/create.php` : `${config.apiUrl}/edit.php?id=${accountId}`;
    const method = mode === 'create' ? 'POST' : 'PUT';

    try {
      // Отправляем запрос на сервер для создания/обновления аккаунта
      const response = await fetch(url, {
        method: method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData),
      });

      // Проверка успешности запроса
      if (!response.ok) {
        const errorData = await response.json();
        // Выбрасываем ошибку с текстом из ответа сервера
        throw new Error(errorData.error || (mode === 'create' ? 'Ошибка при создании аккаунта' : 'Ошибка при обновлении аккаунта'));
      }

      // Перенаправляем на список аккаунтов после успешного создания/обновления
      navigate('/');
    } catch (err) {
      // Обработка ошибки при отправке запроса
      setError((err as Error).message);
    }
  };

  // Обработчик создания новой компании
  const handleCreateCompany = async () => {
    try {
      // Отправляем запрос на создание новой компании
      const response = await fetch(`${config.apiUrl}/companies.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          name: newCompanyName,
          address: newCompanyAddress,
        }),
      });

      // Проверка успешности запроса
      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.error || 'Ошибка при создании компании');
      }

      // Получаем данные созданной компании
      const data = await response.json();
      const newCompany: Company = {
        id: data.data.id,
        name: newCompanyName,
        address: newCompanyAddress,
      };

      // Обновляем список компаний, добавляя новую компанию
      setCompanies([...companies, newCompany]);
      // Устанавливаем ID новой компании в поле company_id формы
      setFormData({ ...formData, company_id: newCompany.id });
      // Сбрасываем поля названия и адреса новой компании
      setNewCompanyName('');
      setNewCompanyAddress('');
      // Закрываем диалог создания компании
      setShowCompanyDialog(false);
    } catch (err) {
      // Обработка ошибки при создании компании
      setError((err as Error).message);
    }
  };

  // JSX для рендеринга формы
  return (
    <Container maxWidth="sm">
      {/* Заголовок формы */}
      <Typography variant="h4" align="center" gutterBottom>
        {mode === 'create' ? 'Создать аккаунт' : 'Редактировать аккаунт'}
      </Typography>

      {/* Отображение сообщения об ошибке, если оно есть */}
      {error && (
        <Typography color="error" align="center" gutterBottom>
          {error}
        </Typography>
      )}

      {/* Форма */}
      <form onSubmit={handleSubmit}>
        {/* Grid для полей формы */}
        <Grid container spacing={2}>
          {/* Поле "Имя" */}
          <InputTextField
            label="Имя"
            name="first_name"
            value={formData.first_name}
            onChange={handleChange}
            required
          />
          {/* Поле "Фамилия" */}
          <InputTextField
            label="Фамилия"
            name="last_name"
            value={formData.last_name}
            onChange={handleChange}
            required
          />
          {/* Поле "Email" */}
          <InputTextField
            label="Email"
            name="email"
            value={formData.email}
            onChange={handleChange}
            required
          />
          {/* Поле "Должность" */}
          <InputTextField
            label="Должность"
            name="position"
            value={formData.position}
            onChange={handleChange}
          />
          {/* Поле "Телефон 1" */}
          <InputTextField
            label="Телефон 1"
            name="phone_1"
            value={formData.phone_1}
            onChange={handleChange}
          />
          {/* Поле "Телефон 2" */}
          <InputTextField
            label="Телефон 2"
            name="phone_2"
            value={formData.phone_2}
            onChange={handleChange}
          />
          {/* Поле "Телефон 3" */}
          <InputTextField
            label="Телефон 3"
            name="phone_3"
            value={formData.phone_3}
            onChange={handleChange}
          />

          {/* Select для выбора компании */}
          <Grid item xs={12}>
            <FormControl fullWidth>
              <InputLabel id="company-select-label">Компания</InputLabel>
              <Select
                labelId="company-select-label"
                id="company-select"
                name="company_id"
                value={formData.company_id || ''}
                onChange={handleCompanyChange}
                label="Компания"
              >
                {/* Пункт для выбора пустой компании */}
                <MenuItem value="">
                  <em>Выберите компанию</em>
                </MenuItem>
                {/* Пункт для открытия диалога создания новой компании */}
                <MenuItem onClick={() => setShowCompanyDialog(true)}>
                    Создать новую компанию
                  </MenuItem>
                {/* Отображение списка компаний в Select */}
                {companies.map((company) => (
                  <MenuItem key={company.id} value={company.id.toString()}>
                    {company.name}
                  </MenuItem>
                ))}
              </Select>
            </FormControl>
          </Grid>
        </Grid>

        {/* Кнопка отправки формы */}
        <Button type="submit" variant="contained" color="primary" fullWidth style={{ marginTop: '20px' }}>
          {mode === 'create' ? 'Создать' : 'Сохранить'}
        </Button>
      </form>

      {/* Диалог для создания новой компании */}
      <Dialog open={showCompanyDialog} onClose={() => setShowCompanyDialog(false)}>
        {/* Заголовок диалога */}
        <DialogTitle>Создать новую компанию</DialogTitle>
        {/* Содержимое диалога (поля для ввода названия и адреса компании) */}
        <DialogContent>
          <TextField
            autoFocus
            margin="dense"
            id="name"
            label="Название компании"
            type="text"
            fullWidth
            value={newCompanyName}
            onChange={(e) => setNewCompanyName(e.target.value)}
          />
          <TextField
            margin="dense"
            id="address"
            label="Адрес"
            type="text"
            fullWidth
            value={newCompanyAddress}
            onChange={(e) => setNewCompanyAddress(e.target.value)}
          />
        </DialogContent>
        {/* Кнопки диалога */}
        <DialogActions>
          <Button onClick={() => setShowCompanyDialog(false)}>Отмена</Button>
          <Button onClick={handleCreateCompany} color="primary">
            Создать
          </Button>
        </DialogActions>
      </Dialog>
    </Container>
  );
};

export default AccountForm;