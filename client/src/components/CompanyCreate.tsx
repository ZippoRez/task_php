import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { 
  TextField, Button, Container, Typography, Grid 
} from '@mui/material';
import InputTextField from './InputTextField'; // Предполагается, что это кастомный компонент TextField
import config from '../config'; // Конфигурационный файл (должен содержать apiUrl)

// Компонент для создания новой компании
const CompanyCreate: React.FC = () => {
  const navigate = useNavigate(); // Хук для навигации

  // Состояния для хранения данных компании
  const [name, setName] = useState(''); // Название компании
  const [address, setAddress] = useState(''); // Адрес компании
  const [error, setError] = useState<string | null>(null); // Сообщение об ошибке

  // Обработчик отправки формы
  const handleSubmit = async (event: React.FormEvent) => {
    event.preventDefault(); // Предотвращаем стандартную отправку формы
    setError(null); // Сбрасываем сообщение об ошибке

    // Создаем объект с данными новой компании
    const newCompany = {
      name: name, 
      address: address
    }; 

    try {
      // Отправляем POST-запрос на API для создания компании
      const response = await fetch(`${config.apiUrl}/companies.php`, { 
        method: 'POST', 
        headers: { 'Content-Type': 'application/json' }, 
        body: JSON.stringify(newCompany),
      }); 

      // Проверяем успешность запроса
      if (!response.ok) {
        // Если ошибка, парсим JSON ответа и выбрасываем исключение
        const errorData = await response.json();
        throw new Error(errorData.error || 'Ошибка при создании компании'); 
      }

      // После успешного создания перенаправляем на страницу со списком компаний
      navigate('/companies'); 

    } catch (err) {
      // Устанавливаем сообщение об ошибке в состояние
      setError((err as Error).message); 
    }
  };

  // JSX для рендеринга формы создания компании
  return (
    <Container maxWidth="sm">
      {/* Заголовок */}
      <Typography variant="h4" align="center" gutterBottom>
        {/* Кнопка "Назад" к списку компаний */}
        <Button component={Link} to="/companies" variant="contained" color="primary" sx={{ float: 'left' }}>
          ←
        </Button>
        Создать компанию
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
          {/* Поле ввода названия компании */}
          <InputTextField 
            label="Название компании"
            name="name"
            value={name}
            onChange={(e) => setName(e.target.value)}
            required 
          />
          {/* Поле ввода адреса компании */}
          <InputTextField 
            label="Адрес компании"
            name="address"
            value={address}
            onChange={(e) => setAddress(e.target.value)} 
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

export default CompanyCreate;