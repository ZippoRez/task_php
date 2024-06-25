import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom'; //  Для перенаправления после создания
import { 
  Button, Container, Typography, Grid 
} from '@mui/material'; // Компоненты Material-UI
import { Account } from '../types/Account'; // Тип данных Account
import InputTextField from './InputTextField'; // Предположительно, компонент для текстового поля
import config from '../config'; // Конфигурация приложения (API URL)
import { Link } from 'react-router-dom'; //  Для ссылки на главную страницу

// Компонент для создания нового аккаунта
const AccountCreate: React.FC = () => {
  // Хук для перенаправления
  const navigate = useNavigate(); 

  // Состояние для хранения данных формы 
  const [formData, setFormData] = useState<Account>({
    id: 0, //  ID  устанавливается сервером
    first_name: '',
    last_name: '',
    email: '',
    company_name: '',
    position: '',
    phone_1: '',
    phone_2: '',
    phone_3: '',
    deleted_at: '', // Поле для "мягкого" удаления (скорее всего,  не нужно в форме создания) 
  });

  // Состояние для хранения сообщения об ошибке
  const [error, setError] = useState<string | null>(null);

  // Обработчик изменения значений в полях формы
  const handleChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setFormData({
      ...formData, //  Копируем текущие данные
      [event.target.name]: event.target.value, //  Обновляем значение поля
    });
  };

  // Обработчик отправки формы
  const handleSubmit = async (event: React.FormEvent) => {
    event.preventDefault(); //  Предотвращаем стандартную отправку формы
    setError(null); //  Сбрасываем сообщение об ошибке

    try {
      // Отправляем POST запрос на сервер
      const response = await fetch(`${config.apiUrl}/create.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData), // Отправляем данные формы в JSON 
      });

      // Проверяем,  успешно ли выполнился запрос
      if (!response.ok) {
        // Если нет,  получаем данные об ошибке
        const errorData = await response.json();
        // Генерируем исключение с сообщением об ошибке
        throw new Error(errorData.error || 'Ошибка при создании аккаунта'); 
      }

      // Если запрос успешен,  перенаправляем на главную страницу
      navigate('/'); 
    } catch (err) {
      // Обрабатываем исключения (ошибки сети или сервера)
      setError((err as Error).message); 
    }
  };

  // JSX для отрисовки формы
  return (
    <Container maxWidth="sm">
      {/* Заголовок */}
      <Typography variant="h4" align="center" gutterBottom>
        {/* Кнопка "Назад" */}
        <Button component={Link} to="/" variant="contained" color="primary" sx={{float:'left'}}>
          ←
        </Button>
        Создать аккаунт
      </Typography>

      {/* Вывод сообщения об ошибке,  если оно есть */}
      {error && (
        <Typography color="error" align="center" gutterBottom>
          {error}
        </Typography>
      )}

      {/* Форма создания аккаунта */}
      <form onSubmit={handleSubmit}>
        <Grid container spacing={2}>
          {/* Поля ввода (используется компонент InputTextField) */}
          <InputTextField 
            label="Имя" 
            name="first_name" 
            value={formData.first_name} 
            onChange={handleChange} 
            required 
          />
          <InputTextField 
            label="Фамилия" 
            name="last_name" 
            value={formData.last_name} 
            onChange={handleChange} 
            required 
          />
          <InputTextField 
            label="Email" 
            name="email" 
            value={formData.email} 
            onChange={handleChange} 
            required 
          />
          <InputTextField 
            label="Название компании" 
            name="company_name" 
            value={formData.company_name} 
            onChange={handleChange}  
          />
          <InputTextField 
            label="Должность" 
            name="position" 
            value={formData.position} 
            onChange={handleChange}  
          />
          <InputTextField 
            label="Телефон 1" 
            name="phone_1" 
            value={formData.phone_1} 
            onChange={handleChange}  
          />
          <InputTextField 
            label="Телефон 2" 
            name="phone_2" 
            value={formData.phone_2} 
            onChange={handleChange}  
          />
          <InputTextField 
            label="Телефон 3" 
            name="phone_3" 
            value={formData.phone_3} 
            onChange={handleChange}  
          />
        </Grid>

        {/* Кнопка "Создать" */}
        <Button type="submit" variant="contained" color="primary" fullWidth style={{ marginTop: '20px' }}>
          Создать
        </Button>
      </form>
    </Container>
  );
};

export default AccountCreate;