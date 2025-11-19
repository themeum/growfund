const ActivityLog = ({
  icon,
  created_at,
  message,
}: {
  icon: React.ReactNode;
  message: React.ReactNode;
  created_at: string;
}) => {
  return (
    <div className="growfund-flex growfund-items-start growfund-gap-3">
      {icon}
      <div className="growfund-grid growfund-gap-2">
        {message}
        <div className="growfund-typo-tiny growfund-font-medium growfund-text-fg-muted">{created_at}</div>
      </div>
    </div>
  );
};

export default ActivityLog;
