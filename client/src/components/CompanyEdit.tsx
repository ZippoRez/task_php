import React, { useState, useEffect } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { 
    TextField, Button, Container, Typography, Grid 
} from '@mui/material';
import InputTextField from './InputTextField'; // Предполагается, что это кастомный компонент TextField
import config from '../config'; // Конфигурационный файл (должен содержать apiUrl)

// Компонент для редактирования компании
const CompanyEdit: React.FC = () => {
  // Получаем ID компании из параметров URL
  const { id } = useParams<{ id: string }>(); 
  // Хук для навигации
  const navigate = useNavigate(); 
  // Преобразуем ID из строки в число
  const companyId = id ? parseInt(id,  10) : 0; 

  // Состояния для данных компании
  const [name,  setName] = useState(''); 
  const [address,  setAddress] = useState(''); 
  // Состояния для ошибки и загрузки
  const [error,  setError] = useState<string | null>(null); 
  const [loading, setLoading] = useState(true);

  // useEffect для загрузки данных компании при монтировании компонента
  useEffect(() => {
    const fetchData = async () => {
      try {
        // Запрос на получение данных компании по API
        const response = await fetch(`${config.apiUrl}/company.php?id=${companyId}`); 
        // Проверка ответа
        if (!response.ok) {
          throw new Error('Ошибка при загрузке данных компании'); 
        }
        // Парсим JSON ответа
        const data = await response.json(); 
        // Устанавливаем данные компании в состояния
        setName(data.data.name);
        setAddress(data.data.address); 
      } catch (err) {
        // Обработка ошибки при загрузке данных
        setError('Ошибка при загрузке данных компании'); 
        console.error(err); 
      } finally {
        // Устанавливаем loading в false после завершения загрузки (успешной или неуспешной)
        setLoading(false); 
      }
    }; 

    // Вызов функции для загрузки данных
    fetchData(); 
  },  [companyId]); 

  // Обработчик отправки формы
  const handleSubmit = async (event: React.FormEvent) => {
    // Предотвращаем стандартную отправку формы
    event.preventDefault(); 
    // Сбрасываем сообщение об ошибке
    setError(null); 

    // Создаем объект с обновленными данными компании
    const updatedCompany = {
      id: companyId, 
      name: name, 
      address: address
    }; 

    try {
      // Отправляем PUT-запрос на API для обновления компании
      const response = await fetch(`${config.apiUrl}/company.php?id=${companyId}`,  {
        method: 'PUT', 
        headers: { 'Content-Type': 'application/json' }, 
        body: JSON.stringify(updatedCompany) 
      });
      // Проверка ответа
      if (!response.ok) {
        // Если ошибка, парсим JSON ответа и выбрасываем исключение
        const errorData = await response.json(); 
        throw new Error(errorData.error ||  'Ошибка при обновлении компании'); 
      }

      // После успешного обновления перенаправляем на страницу со списком компаний
      navigate('/companies'); 

    } catch (err) {
      // Обработка ошибки при обновлении
      setError((err as Error).message); 
    }
  };

  // Условный рендеринг в зависимости от состояния загрузки и ошибки
  if (loading) {
    return <Typography variant="body1">Загрузка данных...</Typography>;
  }

  if (error) {
    return <Typography color="error" variant="body1">Ошибка: {error}</Typography>;
  }

  // JSX для рендеринга формы редактирования компании
  return (
    <Container maxWidth="sm">
      <Typography variant="h4" align="center" gutterBottom>
        {/* Кнопка "Назад" к списку компаний */}
        <Button component={Link} to="/companies" variant="contained" color="primary" sx={{ float: 'left' }}>
          ←
        </Button>
        Редактировать компанию
      </Typography>

      {/* Отображение сообщения об ошибке, если оно есть */}
      {error && (
        <Typography color="error" align="center" gutterBottom>
          {error}
        </Typography>
      )}

      {/* Форма редактирования */}
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

        {/* Кнопка "Сохранить" */}
        <Button type="submit" variant="contained" color="primary" fullWidth style={{ marginTop: '20px' }}>
          Сохранить
        </Button> 
      </form> 
    </Container>
  ); 
};

export default CompanyEdit;