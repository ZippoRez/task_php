import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { TableRow, TableCell, Button, Typography } from '@mui/material';
import { Account } from '../types/Account';
import { formatDistanceToNow, differenceInSeconds } from 'date-fns'; // Функции для работы с датами
import { ru } from 'date-fns/locale'; // Локализация для date-fns
import config from '../config'; // Конфигурационный файл

// Интерфейс для пропсов компонента AccountRow
interface AccountRowProps {
  account: Account; // Данные аккаунта
  onDelete: (id: number) => void; // Функция для удаления аккаунта
  onRestore?: (id: number) => void; // Функция для восстановления аккаунта (необязательная)
}

// Компонент AccountRow для отображения строки с данными аккаунта в таблице
const AccountRow: React.FC<AccountRowProps> = ({
  account,
  onDelete,
  onRestore,
}) => {
  // Вычисление времени до окончательного удаления аккаунта (72 часа)
  const timeUntilDeletion = account.deleted_at
    ? 72 * 60 * 60 -
      differenceInSeconds(new Date(), new Date(account.deleted_at))
    : null;

  // Состояние для хранения названия компании
  const [companyName, setCompanyName] = useState('');

  // Эффект для загрузки названия компании по ID
  useEffect(() => {
    const fetchCompanyName = async () => {
      if (account.company_id) {
        try {
          // Запрос данных компании по API
          const response = await fetch(`${config.apiUrl}/company.php?id=${account.company_id}`);
          // Проверка ответа
          if (!response.ok) {
            throw new Error('Ошибка при загрузке данных компании');
          }
          // Получение данных компании из ответа
          const data = await response.json();
          // Установка названия компании в состояние
          setCompanyName(data.data.name);
        } catch (err) {
          // Обработка ошибки
          console.error('Ошибка при получении названия компании:', err);
        }
      }
    };

    // Вызов функции для загрузки названия компании
    fetchCompanyName();
  }, [account.company_id]);

  // JSX для отображения строки аккаунта
  return (
    <TableRow key={account.id}>
      {/* Ячейка с информацией об удалении, если аккаунт удален */}
      {account.deleted_at && onRestore && (
        <TableCell>
          <Typography variant="caption" color="textSecondary">
            Удалено{' '}
            {formatDistanceToNow(new Date(account.deleted_at), {
              addSuffix: true,
              locale: ru,
            })}
            {/* Отображение времени до окончательного удаления, если оно есть */}
            {timeUntilDeletion !== null && timeUntilDeletion > 0 && (
              <span>
                , остаётся:{' '}
                {formatDistanceToNow(new Date(Date.now() + timeUntilDeletion * 1000), { locale: ru })}
              </span>
            )}
          </Typography>
        </TableCell>
      )}
      {/* Ячейки с данными аккаунта */}
      <TableCell>{account.id}</TableCell>
      <TableCell>{account.first_name}</TableCell>
      <TableCell>{account.last_name}</TableCell>
      <TableCell>{account.email}</TableCell>
      <TableCell>{account.position}</TableCell>
      <TableCell>{account.phone_1}</TableCell>
      <TableCell>{account.phone_2}</TableCell>
      <TableCell>{account.phone_3}</TableCell>
      {/* Ячейка с названием компании */}
      <TableCell>
        {account.company_id ? (
          <Link to={`/companies/${account.company_id}`}>
            {companyName}
          </Link>
        ) : (
          '-'
        )}
      </TableCell>
      {/* Ячейка с кнопками действий */}
      <TableCell>
        {/* Кнопка "Редактировать" (отображается, если аккаунт не удален) */}
        {!account.deleted_at && (
          <>
            <Button component={Link} to={`/edit/${account.id}`}>
              Редактировать
            </Button>
          </>
        )}

        {/* Кнопка "Восстановить" (отображается, если аккаунт удален и есть функция восстановления) */}
        {account.deleted_at && onRestore && (
          <Button onClick={() => onRestore(account.id)} color="success">
            Восстановить
          </Button>
        )}
        
        {/* Кнопка "Удалить" */}
        <Button onClick={() => onDelete(account.id)} color="error">
          Удалить
        </Button>
      </TableCell>
    </TableRow>
  );
};

export default AccountRow;